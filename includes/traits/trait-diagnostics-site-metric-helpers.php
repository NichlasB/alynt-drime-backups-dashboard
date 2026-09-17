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
