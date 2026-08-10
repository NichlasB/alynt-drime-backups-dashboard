<?php
/**
 * Diagnostics site metrics helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds site count and recent polling diagnostics.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Site_Metrics {
	/**
	 * Builds site count diagnostics.
	 *
	 * @param array<int,array<string,mixed>> $sites Sites.
	 * @param array<int,array<string,mixed>> $snapshots Snapshots keyed by site ID.
	 * @param int                            $now Current Unix timestamp.
	 * @return array<string,mixed>
	 */
	private function count_diagnostics( array $sites, array $snapshots, $now ) {
		$counts = array(
			'total_sites'         => count( $sites ),
			'polling_ready'       => 0,
			'due_now'             => 0,
			'missing_credentials' => 0,
			'paused'              => 0,
			'with_failures'       => 0,
			'statuses'            => array(),
		);

		foreach ( $sites as $site ) {
			$site_id  = isset( $site['id'] ) ? (int) $site['id'] : 0;
			$snapshot = isset( $snapshots[ $site_id ] ) ? $snapshots[ $site_id ] : null;
			$status   = $this->classifier->classify( $site, $snapshot, $now );
			$category = isset( $status['category'] ) ? (string) $status['category'] : 'unknown';

			if ( ! isset( $counts['statuses'][ $category ] ) ) {
				$counts['statuses'][ $category ] = 0;
			}

			++$counts['statuses'][ $category ];

			if ( ! empty( $site['paused_at'] ) ) {
				++$counts['paused'];
			}

			if ( $this->is_polling_ready( $site ) ) {
				++$counts['polling_ready'];

				if ( $this->is_due_now( $site, $now ) ) {
					++$counts['due_now'];
				}
			} elseif ( $this->is_enrolled_for_polling( $site ) && empty( $site['paused_at'] ) && ! $this->has_polling_credentials( $site ) ) {
				++$counts['missing_credentials'];
			}

			if ( ! empty( $site['consecutive_failures'] ) ) {
				++$counts['with_failures'];
			}
		}

		ksort( $counts['statuses'] );

		return $counts;
	}

	/**
	 * Builds recent safe polling outcomes.
	 *
	 * @param array<int,array<string,mixed>> $sites Sites.
	 * @param int                            $limit Maximum rows.
	 * @return array<int,array<string,mixed>>
	 */
	private function recent_poll_outcomes( array $sites, $limit = 8 ) {
		$recent = array();

		foreach ( $sites as $site ) {
			if ( empty( $site['last_poll_attempt_at'] ) ) {
				continue;
			}

			$recent[] = array(
				'site_id'              => isset( $site['id'] ) ? (int) $site['id'] : 0,
				'site_label'           => $this->site_name( $site ),
				'enrollment_status'    => isset( $site['enrollment_status'] ) ? sanitize_key( $site['enrollment_status'] ) : '',
				'overall_status'       => isset( $site['overall_status'] ) ? sanitize_key( $site['overall_status'] ) : '',
				'last_poll_attempt_at' => (string) $site['last_poll_attempt_at'],
				'last_seen_at'         => isset( $site['last_seen_at'] ) ? (string) $site['last_seen_at'] : '',
				'next_poll_at'         => isset( $site['next_poll_at'] ) ? (string) $site['next_poll_at'] : '',
				'consecutive_failures' => isset( $site['consecutive_failures'] ) ? max( 0, (int) $site['consecutive_failures'] ) : 0,
				'last_error_code'      => isset( $site['last_error_code'] ) ? sanitize_key( $site['last_error_code'] ) : '',
				'last_error_summary'   => isset( $site['last_error_summary'] ) ? sanitize_text_field( $site['last_error_summary'] ) : '',
			);
		}

		usort(
			$recent,
			function ( $left, $right ) {
				return $this->timestamp( $right['last_poll_attempt_at'] ) <=> $this->timestamp( $left['last_poll_attempt_at'] );
			}
		);

		return array_slice( $recent, 0, max( 1, min( 50, (int) $limit ) ) );
	}

	/**
	 * Determines whether a site is eligible for polling.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return bool
	 */
	private function is_polling_ready( array $site ) {
		return $this->is_enrolled_for_polling( $site )
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
		return ! empty( $site['polling_key_id'] ) && ! empty( $site['polling_secret_ciphertext'] );
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
