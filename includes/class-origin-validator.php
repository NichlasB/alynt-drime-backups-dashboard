<?php
/**
 * Public HTTPS origin validation helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalizes and validates public HTTPS origins for v1 pairing and polling.
 *
 * @since 0.1.0
 */
class Alynt_Drime_Backups_Dashboard_Origin_Validator {
	/**
	 * Normalizes a public HTTPS origin.
	 *
	 * @param string $origin Raw origin or URL.
	 * @return string Empty string when invalid.
	 */
	public function normalize_public_https_origin( $origin ) {
		$origin = trim( (string) $origin );
		$parts  = parse_url( $origin );

		if ( ! is_array( $parts ) || empty( $parts['scheme'] ) || empty( $parts['host'] ) ) {
			return '';
		}

		if ( 'https' !== strtolower( (string) $parts['scheme'] ) ) {
			return '';
		}

		if ( isset( $parts['user'] ) || isset( $parts['pass'] ) || isset( $parts['query'] ) || isset( $parts['fragment'] ) ) {
			return '';
		}

		if ( isset( $parts['path'] ) && '' !== $parts['path'] && '/' !== $parts['path'] ) {
			return '';
		}

		if ( isset( $parts['port'] ) && 443 !== absint( $parts['port'] ) ) {
			return '';
		}

		$host = strtolower( rtrim( (string) $parts['host'], '.' ) );

		if ( ! $this->is_public_hostname( $host ) ) {
			return '';
		}

		return 'https://' . $host;
	}

	/**
	 * Builds the fixed uploader status endpoint for a canonical origin.
	 *
	 * @param string $origin Canonical origin.
	 * @return string
	 */
	public function status_endpoint_for_origin( $origin ) {
		$origin = $this->normalize_public_https_origin( $origin );

		return '' === $origin ? '' : $origin . '/wp-json/alynt-drime-backups-uploader/v1/status';
	}

	/**
	 * Checks whether a host is an allowed public hostname for v1.
	 *
	 * @param string $host Hostname.
	 * @return bool
	 */
	private function is_public_hostname( $host ) {
		if ( '' === $host || 'localhost' === $host || false !== strpos( $host, '..' ) || preg_match( '/(^|\.)local$/', $host ) ) {
			return false;
		}

		if ( false !== filter_var( $host, FILTER_VALIDATE_IP ) ) {
			return false;
		}

		return (bool) preg_match( '/^[a-z0-9][a-z0-9.-]*[a-z0-9]$/', $host );
	}
}
