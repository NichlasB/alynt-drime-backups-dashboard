<?php
/**
 * Remote action dispatcher helper trait.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.26
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *
Handles safe same-origin transport and response normalization.
 *
 * @since 0.1.26
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_Transport {


	/**
	 * Allows this dashboard to request its own fixed signed action endpoint.
	 *
	 * Some managed hosts resolve the site's own public hostname to loopback or
	 * a private address from the server itself. That remains unsafe for
	 * arbitrary client sites, but is acceptable for the exact same-origin
	 * dashboard self-action case because the origin must match this dashboard's
	 * public HTTPS home URL and the request remains fixed, signed, bounded, and
	 * opt-in-gated to the V2.1 scan/upload-now route.
	 *
	 * @since 0.1.18
	 *
	 * @param string $origin Candidate client origin.
	 * @return bool
	 */
	private function is_same_origin_self_action( $origin ) {
		if ( ! function_exists( 'home_url' ) ) {
			return false;
		}

		$client_origin    = $this->origins->normalize_public_https_origin( $origin );
		$dashboard_origin = $this->origins->normalize_public_https_origin( home_url( '/', 'https' ) );

		return '' !== $client_origin && '' !== $dashboard_origin && hash_equals( $dashboard_origin, $client_origin );
	}

	/**
	 * Posts a signed intent and returns the safe response.
	 *
	 * @param array<string,mixed> $prepared Prepared request.
	 * @return array<string,mixed>|WP_Error
	 */
	private function post_intent( array $prepared ) {
		$http_client = $this->http_client;

		if ( null === $http_client ) {
			if ( ! function_exists( 'wp_safe_remote_post' ) ) {
				return new WP_Error( 'transport_unavailable', __( 'WordPress HTTP transport is not available.', 'alynt-drime-backups-dashboard' ) );
			}

			$http_client = 'wp_safe_remote_post';
		}

		$response = call_user_func(
			$http_client,
			(string) $prepared['url'],
			array(
				'method'              => 'POST',
				'timeout'             => self::DEFAULT_TIMEOUT,
				'redirection'         => 0,
				'limit_response_size' => self::MAX_RESPONSE_BYTES,
				'reject_unsafe_urls'  => ! empty( $prepared['reject_unsafe_urls'] ),
				'headers'             => array(
					'Accept'                  => 'application/json',
					'Content-Type'            => 'application/json',
					'Cache-Control'           => 'no-store',
					'X-Adbd-Action-Key-Id'    => (string) $prepared['key_id'],
					'X-Adbd-Action-Signature' => (string) $prepared['signature'],
					'X-Adbd-Action-Signed-At' => (string) $prepared['signed_at'],
				),
				'body'                => (string) $prepared['body_json'],
			)
		);

		if ( is_wp_error( $response ) ) {
			return new WP_Error( $response->get_error_code(), __( 'The signed request could not reach the client action endpoint.', 'alynt-drime-backups-dashboard' ) );
		}

		$code = $this->response_code( $response );
		$body = $this->response_body( $response );

		if ( strlen( $body ) > self::MAX_RESPONSE_BYTES ) {
			return new WP_Error( 'remote_action_response_too_large', __( 'The client action response exceeded the dashboard size limit.', 'alynt-drime-backups-dashboard' ) );
		}

		$payload = json_decode( $body, true, 32 );
		if ( ! is_array( $payload ) ) {
			return new WP_Error( 'remote_action_response_invalid', __( 'The client action response was not valid JSON.', 'alynt-drime-backups-dashboard' ) );
		}

		$expected_action_id = isset( $prepared['body']['action_id'] ) ? (string) $prepared['body']['action_id'] : '';
		$response_action_id = isset( $payload['action_id'] ) ? sanitize_text_field( (string) $payload['action_id'] ) : '';

		if ( '' === $expected_action_id || ! hash_equals( $expected_action_id, $response_action_id ) ) {
			return new WP_Error( 'remote_action_response_mismatch', __( 'The client action response did not match the dashboard request.', 'alynt-drime-backups-dashboard' ) );
		}

		$state = $this->capabilities->sanitize_state( isset( $payload['state'] ) ? (string) $payload['state'] : '' );
		if ( 'queued_for_dispatch' === $state ) {
			return new WP_Error( 'remote_action_response_invalid', __( 'The client action response did not include a supported action state.', 'alynt-drime-backups-dashboard' ) );
		}

		if ( $code < 200 || $code >= 500 ) {
			return new WP_Error( 'remote_action_http_status', __( 'The client action endpoint returned an unsupported HTTP status.', 'alynt-drime-backups-dashboard' ) );
		}

		return array(
			'state'       => $state,
			'code'        => isset( $payload['result_code'] ) ? sanitize_key( (string) $payload['result_code'] ) : ( isset( $payload['code'] ) ? sanitize_key( (string) $payload['code'] ) : '' ),
			'summary'     => isset( $payload['result_summary'] ) ? $this->bounded_summary( (string) $payload['result_summary'] ) : ( isset( $payload['summary'] ) ? $this->bounded_summary( (string) $payload['summary'] ) : '' ),
			'retry_after' => isset( $payload['retry_after'] ) ? max( 0, absint( $payload['retry_after'] ) ) : 0,
		);
	}

	/**
	 * Extracts HTTP response code.
	 *
	 * @param mixed $response Response.
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
	 * @param mixed $response Response.
	 * @return string
	 */
	private function response_body( $response ) {
		if ( function_exists( 'wp_remote_retrieve_body' ) ) {
			return (string) wp_remote_retrieve_body( $response );
		}

		return isset( $response['body'] ) ? (string) $response['body'] : '';
	}
}
