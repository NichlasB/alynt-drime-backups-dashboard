<?php
/**
 * Enrollment REST response helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds enrollment REST responses and safe failure events.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Enrollment_REST_Responses {
	/**
	 * Builds an error response.
	 *
	 * @param string $code Error code.
	 * @param string $message Error message.
	 * @param int    $status HTTP status.
	 * @return WP_Error
	 */
	private function error( $code, $message, $status ) {
		return new WP_Error( $code, $message, array( 'status' => (int) $status ) );
	}

	/**
	 * Builds and logs a safe enrollment error.
	 *
	 * @param string                   $code Error code.
	 * @param string                   $message Error message.
	 * @param int                      $status HTTP status.
	 * @param array<string,mixed>|null $site Site row.
	 * @return WP_Error
	 */
	private function enrollment_error( $code, $message, $status, $site = null ) {
		$error = $this->error( $code, $message, $status );

		$this->log_enrollment_failure( $error, is_array( $site ) ? $site : array() );

		return $error;
	}

	/**
	 * Records a redacted enrollment failure event.
	 *
	 * @param WP_Error                 $error Error.
	 * @param array<string,mixed>|null $site Site row.
	 * @return void
	 */
	private function log_enrollment_failure( $error, $site = null ) {
		$context = array();

		if ( is_array( $site ) && ! empty( $site ) ) {
			$context['dashboard_site_id'] = isset( $site['id'] ) ? (int) $site['id'] : 0;
			$context['enrollment_status'] = isset( $site['enrollment_status'] ) ? sanitize_key( $site['enrollment_status'] ) : '';
		}

		$this->event_log->log( 'warning', 'rest', $error->get_error_code(), $error->get_error_message(), $context );
	}

	/**
	 * Builds a REST response or array fallback for tests.
	 *
	 * @param array<string,mixed> $data Response data.
	 * @param int                 $status HTTP status.
	 * @return WP_REST_Response|array<string,mixed>
	 */
	private function response( array $data, $status ) {
		if ( class_exists( 'WP_REST_Response' ) ) {
			$response = new WP_REST_Response( $data, (int) $status );
			$response->header( 'Cache-Control', 'no-store' );

			return $response;
		}

		return array(
			'data'    => $data,
			'status'  => (int) $status,
			'headers' => array(
				'Cache-Control' => 'no-store',
			),
		);
	}
}
