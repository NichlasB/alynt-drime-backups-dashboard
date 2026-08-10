<?php
/**
 * Poller status-check flow.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Performs one-site status checks and failure recording for the poller.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Poller_Status_Check {
	/**
	 * Performs the status check for a loaded site row.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return array<string,mixed>|WP_Error
	 */
	private function check_site_status( array $site ) {
		$site_id = isset( $site['id'] ) ? (int) $site['id'] : 0;
		$auth    = $this->polling_auth_scheme( $site );

		if ( is_wp_error( $auth ) ) {
			if ( ! $this->mark_poll_failure( $site, $auth->get_error_code(), $auth->get_error_message() ) ) {
				return $this->poll_failure_storage_error( $site, $auth );
			}
			$this->log_poll_failure( $site, $auth );
			return $auth;
		}

		$raw_payload = $this->transport->fetch_status_payload( $site, $auth, $this->http_client );

		if ( is_wp_error( $raw_payload ) ) {
			if ( ! $this->mark_poll_failure( $site, $raw_payload->get_error_code(), $raw_payload->get_error_message() ) ) {
				return $this->poll_failure_storage_error( $site, $raw_payload );
			}
			$this->log_poll_failure( $site, $raw_payload );
			return $raw_payload;
		}

		$payload = $this->validator->validate( $raw_payload, isset( $site['site_uuid'] ) ? (string) $site['site_uuid'] : '' );

		if ( is_wp_error( $payload ) ) {
			if ( ! $this->mark_poll_failure( $site, $payload->get_error_code(), $payload->get_error_message() ) ) {
				return $this->poll_failure_storage_error( $site, $payload );
			}
			$this->log_poll_failure( $site, $payload );
			return $payload;
		}

		$status   = $this->classifier->classify(
			array_merge(
				$site,
				array(
					'overall_status' => 'working',
					'last_seen_at'   => gmdate( 'Y-m-d H:i:s' ),
				)
			),
			array(
				'decoded_payload' => $payload,
				'observed_at'     => gmdate( 'Y-m-d H:i:s' ),
				'schema_version'  => 1,
			)
		);
		$snapshot = $this->snapshots->record( $site_id, $payload, $status['category'] );

		if ( is_wp_error( $snapshot ) ) {
			$this->mark_poll_failure( $site, $snapshot->get_error_code(), $snapshot->get_error_message() );
			$this->log_poll_failure( $site, $snapshot );
			return $snapshot;
		}

		if ( $snapshot <= 0 ) {
			$error = new WP_Error( 'snapshot_store_failed', __( 'The dashboard could not store the client status snapshot.', 'alynt-drime-backups-dashboard' ) );
			$this->mark_poll_failure( $site, $error->get_error_code(), $error->get_error_message() );
			$this->log_poll_failure( $site, $error );
			return $error;
		}

		$stored = $this->sites->mark_poll_success(
			$site_id,
			$status['category'],
			isset( $payload['plugin_version'] ) ? (string) $payload['plugin_version'] : '',
			$this->next_poll_after_success( $site )
		);

		if ( ! $stored ) {
			$error = new WP_Error( 'poll_success_store_failed', __( 'The dashboard could not update the site after the status check. Please try again.', 'alynt-drime-backups-dashboard' ) );
			$this->log_poll_failure( $site, $error );
			return $error;
		}

		return array(
			'category'    => $status['category'],
			'label'       => $status['label'],
			'message'     => $status['message'],
			'snapshot_id' => $snapshot,
		);
	}

	/**
	 * Builds the polling authorization header.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return string|WP_Error
	 */
	private function polling_auth_scheme( array $site ) {
		if ( empty( $site['public_id'] ) || empty( $site['polling_key_id'] ) || empty( $site['polling_secret_ciphertext'] ) ) {
			return new WP_Error( 'auth_missing', __( 'The dashboard site does not have a polling credential yet.', 'alynt-drime-backups-dashboard' ) );
		}

		$secret = $this->vault->decrypt( (string) $site['polling_secret_ciphertext'], 'site:' . (string) $site['public_id'] );

		if ( is_wp_error( $secret ) ) {
			return $secret;
		}

		return 'Bearer adb-poll-v1.' . (string) $site['polling_key_id'] . '.' . $secret;
	}

	/**
	 * Marks a poll failure with retry metadata.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @param string              $error_code Error code.
	 * @param string              $summary Safe summary.
	 * @return bool
	 */
	private function mark_poll_failure( array $site, $error_code, $summary ) {
		$failures = isset( $site['consecutive_failures'] ) ? max( 0, (int) $site['consecutive_failures'] ) + 1 : 1;

		return (bool) $this->sites->mark_poll_failure(
			isset( $site['id'] ) ? (int) $site['id'] : 0,
			$error_code,
			$summary,
			$this->next_poll_after_failure( $site, $failures ),
			$failures
		);
	}

	/**
	 * Builds and logs an error when a failed poll cannot persist failure details.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @param WP_Error            $original_error Original poll error.
	 * @return WP_Error
	 */
	private function poll_failure_storage_error( array $site, $original_error ) {
		$error = new WP_Error(
			'poll_failure_store_failed',
			__( 'The status check failed, and the dashboard could not save the failure details.', 'alynt-drime-backups-dashboard' ),
			array(
				'original_error_code' => $original_error->get_error_code(),
			)
		);

		$this->log_poll_failure( $site, $error );

		return $error;
	}

	/**
	 * Records a redacted poll failure event.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @param WP_Error            $error Error.
	 * @return void
	 */
	private function log_poll_failure( array $site, $error ) {
		$this->event_log->log(
			'error',
			'external_api',
			$error->get_error_code(),
			$error->get_error_message(),
			array(
				'dashboard_site_id'    => isset( $site['id'] ) ? (int) $site['id'] : 0,
				'enrollment_status'    => isset( $site['enrollment_status'] ) ? sanitize_key( $site['enrollment_status'] ) : '',
				'overall_status'       => isset( $site['overall_status'] ) ? sanitize_key( $site['overall_status'] ) : '',
				'consecutive_failures' => isset( $site['consecutive_failures'] ) ? max( 0, (int) $site['consecutive_failures'] ) + 1 : 1,
			)
		);
	}
}
