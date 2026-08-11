<?php
/**
 * Safe polling transport foundation.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds and executes fixed read-only status polling requests.
 *
 * @since 0.1.0
 */
class Alynt_Drime_Backups_Dashboard_Safe_Transport {
	const STATUS_ROUTE            = '/wp-json/alynt-drime-backups-uploader/v1/status';
	const DEFAULT_TIMEOUT_SECONDS = 10;
	const MAX_RESPONSE_SIZE_BYTES = 1048576;

	/**
	 * Origin validator.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Origin_Validator
	 */
	private $origins;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Origin_Validator|null $origins Origin validator.
	 */
	public function __construct( $origins = null ) {
		$this->origins = $origins instanceof Alynt_Drime_Backups_Dashboard_Origin_Validator ? $origins : new Alynt_Drime_Backups_Dashboard_Origin_Validator();
	}

	/**
	 * Prepares a safe status polling request descriptor.
	 *
	 * This method intentionally does not perform the HTTP request.
	 *
	 * @since 0.1.0
	 *
	 * @param array<string,mixed> $site Dashboard site row.
	 * @param string              $polling_auth_scheme Authorization header value.
	 * @return array<string,mixed>|WP_Error
	 */
	public function prepare_status_request( array $site, $polling_auth_scheme ) {
		$origin = isset( $site['expected_origin'] ) ? (string) $site['expected_origin'] : '';
		$url    = $this->origins->status_endpoint_for_origin( $origin );

		if ( '' === $url ) {
			return new WP_Error( 'destination_unsafe', __( 'The client status destination is not a supported public HTTPS origin.', 'alynt-drime-backups-dashboard' ) );
		}

		$polling_auth_scheme = trim( (string) $polling_auth_scheme );

		if ( ! preg_match( '/^Bearer adb-poll-v1\.[A-Za-z0-9_-]{8,64}\.[A-Za-z0-9_-]{32,}$/', $polling_auth_scheme ) ) {
			return new WP_Error( 'auth_invalid', __( 'The polling authorization scheme is not valid.', 'alynt-drime-backups-dashboard' ) );
		}

		return array(
			'url'  => $url,
			'args' => array(
				'method'              => 'GET',
				'timeout'             => self::DEFAULT_TIMEOUT_SECONDS,
				'redirection'         => 0,
				'limit_response_size' => self::MAX_RESPONSE_SIZE_BYTES,
				'reject_unsafe_urls'  => true,
				'headers'             => array(
					'Accept'        => 'application/json',
					'Cache-Control' => 'no-store',
					'Authorization' => $polling_auth_scheme,
				),
			),
		);
	}

	/**
	 * Executes the fixed status request and decodes its JSON response.
	 *
	 * @since 0.1.0
	 *
	 * @param array<string,mixed> $site Dashboard site row.
	 * @param string              $polling_auth_scheme Authorization header value.
	 * @param callable|null       $http_client Optional HTTP client override.
	 * @return array<string,mixed>|WP_Error
	 */
	public function fetch_status_payload( array $site, $polling_auth_scheme, $http_client = null ) {
		$request = $this->prepare_status_request( $site, $polling_auth_scheme );

		if ( is_wp_error( $request ) ) {
			return $request;
		}

		if ( null === $http_client ) {
			if ( ! function_exists( 'wp_safe_remote_get' ) ) {
				return new WP_Error( 'transport_unavailable', __( 'WordPress HTTP transport is not available.', 'alynt-drime-backups-dashboard' ) );
			}

			$http_client = 'wp_safe_remote_get';
		}

		$response = call_user_func( $http_client, $request['url'], $request['args'] );

		if ( is_wp_error( $response ) ) {
			if ( $this->is_timeout_error( $response ) ) {
				return new WP_Error( 'transport_timeout', __( 'The client status request timed out. Please try again, or wait for the next scheduled poll.', 'alynt-drime-backups-dashboard' ) );
			}

			return new WP_Error( 'transport_failed', __( 'The client status request could not reach the client site.', 'alynt-drime-backups-dashboard' ) );
		}

		$code = $this->response_code( $response );
		if ( 200 !== $code ) {
			return new WP_Error(
				'transport_http_status',
				sprintf(
					/* translators: %d: HTTP response status code. */
					__( 'The client status endpoint returned HTTP %d instead of HTTP 200.', 'alynt-drime-backups-dashboard' ),
					$code
				),
				array( 'status' => $code )
			);
		}

		$body = $this->response_body( $response );
		if ( strlen( $body ) > self::MAX_RESPONSE_SIZE_BYTES ) {
			return new WP_Error( 'response_too_large', __( 'The client status response exceeded the dashboard size limit.', 'alynt-drime-backups-dashboard' ) );
		}

		$payload = json_decode( $body, true, 64 );
		if ( ! is_array( $payload ) ) {
			return new WP_Error( 'json_invalid', __( 'The client status response was not valid JSON.', 'alynt-drime-backups-dashboard' ) );
		}

		return $payload;
	}

	/**
	 * Extracts HTTP response code.
	 *
	 * @param mixed $response HTTP response.
	 * @return int
	 */
	private function response_code( $response ) {
		if ( function_exists( 'wp_remote_retrieve_response_code' ) ) {
			return (int) wp_remote_retrieve_response_code( $response );
		}

		return isset( $response['response']['code'] ) ? (int) $response['response']['code'] : 0;
	}

	/**
	 * Extracts HTTP response body.
	 *
	 * @param mixed $response HTTP response.
	 * @return string
	 */
	private function response_body( $response ) {
		if ( function_exists( 'wp_remote_retrieve_body' ) ) {
			return (string) wp_remote_retrieve_body( $response );
		}

		return isset( $response['body'] ) ? (string) $response['body'] : '';
	}

	/**
	 * Determines whether a WordPress HTTP error looks like a timeout.
	 *
	 * @param WP_Error $error HTTP error.
	 * @return bool
	 */
	private function is_timeout_error( $error ) {
		$message = strtolower( $error->get_error_message() );
		$code    = strtolower( $error->get_error_code() );

		return false !== strpos( $message, 'timed out' )
			|| false !== strpos( $message, 'timeout' )
			|| false !== strpos( $message, 'operation timed out' )
			|| false !== strpos( $message, 'curl error 28' )
			|| false !== strpos( $code, 'timeout' );
	}
}
