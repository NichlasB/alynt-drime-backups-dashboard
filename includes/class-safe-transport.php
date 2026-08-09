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
 * Builds fixed read-only status polling requests without executing them.
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
}
