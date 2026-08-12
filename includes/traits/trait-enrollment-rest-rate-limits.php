<?php
/**
 * Enrollment REST rate-limit helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tracks bounded enrollment failures for the public pairing endpoint.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Enrollment_REST_Rate_Limits {
	/**
	 * Builds an enrollment failure error and increments the rate-limit counter.
	 *
	 * @param string                   $rate_limit_key Rate-limit transient key.
	 * @param WP_Error                 $error Enrollment error.
	 * @param array<string,mixed>|null $site Site row.
	 * @return WP_Error
	 */
	private function throttled_enrollment_error( $rate_limit_key, $error, $site = null ) {
		$this->record_enrollment_failure( $rate_limit_key );
		$this->log_enrollment_failure( $error, $site );

		return $error;
	}

	/**
	 * Builds a transient key for enrollment failure throttling.
	 *
	 * @param array<string,mixed> $payload Enrollment payload.
	 * @return string
	 */
	private function enrollment_rate_limit_key( array $payload ) {
		$enrollment_id = isset( $payload['enrollment_id'] ) ? $this->sanitize_uuid( (string) $payload['enrollment_id'] ) : '';
		$key_material  = '' === $enrollment_id ? 'missing-enrollment-id' : $enrollment_id;

		return self::RATE_LIMIT_TRANSIENT_PREFIX . hash( 'sha256', $key_material );
	}

	/**
	 * Determines whether an enrollment key is currently rate limited.
	 *
	 * @param string $key Rate-limit transient key.
	 * @return bool
	 */
	private function is_enrollment_rate_limited( $key ) {
		if ( ! function_exists( 'get_transient' ) ) {
			return false;
		}

		$state = get_transient( $key );
		$count = is_array( $state ) && isset( $state['count'] ) ? (int) $state['count'] : (int) $state;

		return $count >= self::RATE_LIMIT_FAILURE_THRESHOLD;
	}

	/**
	 * Records one failed enrollment attempt for throttling.
	 *
	 * @param string $key Rate-limit transient key.
	 * @return void
	 */
	private function record_enrollment_failure( $key ) {
		if ( ! function_exists( 'get_transient' ) || ! function_exists( 'set_transient' ) ) {
			return;
		}

		$state = get_transient( $key );
		$count = is_array( $state ) && isset( $state['count'] ) ? (int) $state['count'] : (int) $state;

		set_transient(
			$key,
			array(
				'count' => min( self::RATE_LIMIT_FAILURE_THRESHOLD, $count + 1 ),
			),
			self::RATE_LIMIT_WINDOW_SECONDS
		);
	}

	/**
	 * Clears enrollment failures after a successful enrollment.
	 *
	 * @param string $key Rate-limit transient key.
	 * @return void
	 */
	private function clear_enrollment_failures( $key ) {
		if ( function_exists( 'delete_transient' ) ) {
			delete_transient( $key );
		}
	}
}
