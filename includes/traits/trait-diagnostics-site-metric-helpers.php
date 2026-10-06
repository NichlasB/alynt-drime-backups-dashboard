<?php
/**
 * Diagnostics site metric utility helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides polling and site utility helpers for diagnostics metrics.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Site_Metric_Helpers {
	/**
	 * Determines whether a site is eligible for polling.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return bool
	 */
	private function is_polling_ready( array $site ) {
		return $this->is_enrolled_for_polling( $site )
			&& empty( $site['archived_at'] )
			&& empty( $site['paused_at'] )
			&& $this->has_polling_credentials( $site );
	}

	/**
	 * Determines whether a site has stored polling credential metadata.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return bool
	 */
	private function has_polling_credentials( array $site ) {
		return ! empty( $site['polling_key_id'] )
			&& (
				! empty( $site['polling_secret_ciphertext'] )
				|| ! empty( $site['has_polling_secret'] )
			);
	}

	/**
	 * Determines whether a site is enrolled enough that polling credentials are expected.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return bool
	 */
	private function is_enrolled_for_polling( array $site ) {
		$status = isset( $site['enrollment_status'] ) ? (string) $site['enrollment_status'] : '';

		return in_array(
			$status,
			array(
				'active',
				Alynt_Drime_Backups_Dashboard_Enrollment_REST_Controller::ENROLLMENT_STATUS_AWAITING_FIRST_POLL,
			),
			true
		);
	}

	/**
	 * Determines whether the site is due for a scheduled poll.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @param int                 $now Current Unix timestamp.
	 * @return bool
	 */
	private function is_due_now( array $site, $now ) {
		if ( empty( $site['next_poll_at'] ) ) {
			return true;
		}

		$next_poll = $this->timestamp( $site['next_poll_at'] );

		return $next_poll <= 0 || $next_poll <= $now;
	}

	/**
	 * Gets a support-safe dashboard record state bucket.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return string
	 */
	private function site_record_state( array $site ) {
		if ( ! empty( $site['archived_at'] ) ) {
			return 'archived';
		}

		$status = isset( $site['enrollment_status'] ) ? sanitize_key( (string) $site['enrollment_status'] ) : '';

		if ( '' === $status ) {
			return 'unknown';
		}

		if ( in_array( $status, array( 'active', 'awaiting_first_poll', 'pending', 'revoked' ), true ) ) {
			return $status;
		}

		return 'other';
	}

	/**
	 * Builds support-safe local removal-readiness diagnostics for an archived record.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return array<string,int>
	 */
	private function local_removal_readiness_diagnostics( array $site ) {
		$site_id                   = isset( $site['id'] ) ? (int) $site['id'] : 0;
		$snapshot_count            = $this->count_repository_rows_for_site( 'snapshots', 'count_for_site', $site_id );
		$action_count              = $this->count_repository_rows_for_site( 'remote_actions', 'count_for_site', $site_id );
		$non_terminal_action_count = $this->count_repository_rows_for_site( 'remote_actions', 'count_non_terminal_for_site', $site_id );
		$is_ready                  = $this->local_removal_readiness_blocking_reason( $site, $non_terminal_action_count ) === '';

		return array(
			'archived_records'         => 1,
			'ready_records'            => $is_ready ? 1 : 0,
			'blocked_records'          => $is_ready ? 0 : 1,
			'retained_snapshot_rows'   => $snapshot_count,
			'retained_action_rows'     => $action_count,
			'non_terminal_action_rows' => $non_terminal_action_count,
		);
	}

	/**
	 * Gets the first support-safe reason an archived record is not removal-ready.
	 *
	 * This intentionally mirrors the Site Detail preview boundary without exposing
	 * row-level labels, domains, credentials, paths, payloads, or raw action data.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @param int                 $non_terminal_action_count Non-terminal action rows.
	 * @return string
	 */
	private function local_removal_readiness_blocking_reason( array $site, $non_terminal_action_count ) {
		$status = isset( $site['enrollment_status'] ) ? sanitize_key( (string) $site['enrollment_status'] ) : '';

		if ( empty( $site['archived_at'] ) ) {
			return 'not_archived';
		}

		if ( 'revoked' !== $status && ! $this->is_expired_pending_local_record( $site ) ) {
			return 'not_terminal_status';
		}

		if ( $this->has_polling_credentials( $site ) ) {
			return 'polling_credentials_present';
		}

		if ( ! empty( $site['action_key_id'] ) || ! empty( $site['action_private_key_ciphertext'] ) ) {
			return 'action_credentials_present';
		}

		if ( ! empty( $site['next_poll_at'] ) ) {
			return 'scheduled_poll_present';
		}

		if ( ! empty( $site['paused_at'] ) ) {
			return 'paused_not_terminal';
		}

		if ( $non_terminal_action_count > 0 ) {
			return 'non_terminal_action_history';
		}

		return '';
	}

	/**
	 * Determines whether a pending local record has expired pairing state.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return bool
	 */
	private function is_expired_pending_local_record( array $site ) {
		$status = isset( $site['enrollment_status'] ) ? sanitize_key( (string) $site['enrollment_status'] ) : '';

		if ( 'pending' !== $status ) {
			return false;
		}

		$expires_at = isset( $site['pairing_expires_at'] ) ? (string) $site['pairing_expires_at'] : '';

		if ( '' === $expires_at ) {
			return true;
		}

		$expires = strtotime( $expires_at );

		return false === $expires || $expires <= time();
	}

	/**
	 * Counts plugin-owned rows through a repository when the method is available.
	 *
	 * @param string $property Repository property name.
	 * @param string $method Repository method name.
	 * @param int    $site_id Site ID.
	 * @return int
	 */
	private function count_repository_rows_for_site( $property, $method, $site_id ) {
		if ( ! isset( $this->{$property} ) || ! is_object( $this->{$property} ) || ! method_exists( $this->{$property}, $method ) ) {
			return 0;
		}

		$repository_class = get_class( $this->{$property} );

		$has_database = isset( $GLOBALS['wpdb'] ) && is_object( $GLOBALS['wpdb'] );
		$is_test_repo = false !== strpos( $repository_class, '_Test_' );

		if ( ! $has_database && ! $is_test_repo ) {
			return 0;
		}

		return max( 0, (int) $this->{$property}->{$method}( $site_id ) );
	}

	/**
	 * Extracts site IDs without depending on WordPress helpers.
	 *
	 * @param array<int,array<string,mixed>> $sites Sites.
	 * @return array<int>
	 */
	private function site_ids( array $sites ) {
		$site_ids = array();

		foreach ( $sites as $site ) {
			if ( ! empty( $site['id'] ) ) {
				$site_ids[] = (int) $site['id'];
			}
		}

		return $site_ids;
	}

	/**
	 * Gets a safe site display name.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return string
	 */
	private function site_name( array $site ) {
		if ( ! empty( $site['site_label'] ) ) {
			return sanitize_text_field( $site['site_label'] );
		}

		if ( ! empty( $site['expected_origin'] ) ) {
			return esc_url_raw( $site['expected_origin'] );
		}

		return __( 'Unnamed site', 'alynt-drime-backups-dashboard' );
	}

	/**
	 * Parses a timestamp.
	 *
	 * @param mixed $value Timestamp-ish value.
	 * @return int
	 */
	private function timestamp( $value ) {
		if ( is_numeric( $value ) ) {
			return (int) $value;
		}

		if ( is_string( $value ) && '' !== $value ) {
			$timestamp = strtotime( $value );

			return false === $timestamp ? 0 : (int) $timestamp;
		}

		return 0;
	}
}
