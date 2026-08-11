<?php
/**
 * Pairing token helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds opaque dashboard-generated pairing token material.
 *
 * @since 0.1.0
 */
class Alynt_Drime_Backups_Dashboard_Pairing_Tokens {
	const TOKEN_PREFIX     = 'adb1.';
	const PROTOCOL_VERSION = 1;

	/**
	 * Creates a random URL-safe secret.
	 *
	 * @since 0.1.0
	 *
	 * @param int $bytes Number of random bytes.
	 * @return string
	 */
	public static function create_secret( $bytes = 32 ) {
		return self::base64url_encode( random_bytes( $bytes ) );
	}

	/**
	 * Formats a one-time pairing token payload.
	 *
	 * The payload is encoded for transport, not encrypted. Secrets are never
	 * intended for URLs, logs, email, or long-term plaintext storage.
	 *
	 * @since 0.1.0
	 *
	 * @param string $enrollment_id Pending enrollment identifier.
	 * @param string $dashboard_origin Canonical dashboard origin.
	 * @param string $expected_client_origin Canonical client origin.
	 * @param string $secret One-time enrollment secret.
	 * @param int    $expires_at Unix timestamp expiry.
	 * @return string
	 */
	public static function format_token( $enrollment_id, $dashboard_origin, $expected_client_origin, $secret, $expires_at ) {
		$payload = array(
			'protocol_version'       => self::PROTOCOL_VERSION,
			'enrollment_id'          => (string) $enrollment_id,
			'dashboard_origin'       => (string) $dashboard_origin,
			'expected_client_origin' => (string) $expected_client_origin,
			'secret'                 => (string) $secret,
			'expires_at'             => gmdate( 'c', (int) $expires_at ),
		);

		return self::TOKEN_PREFIX . self::base64url_encode( wp_json_encode( $payload ) );
	}

	/**
	 * Creates display-once token material and the verifier to store.
	 *
	 * @since 0.1.0
	 *
	 * @param string $enrollment_id Pending enrollment identifier.
	 * @param string $dashboard_origin Canonical dashboard origin.
	 * @param string $expected_client_origin Canonical client origin.
	 * @param int    $expires_at Unix timestamp expiry.
	 * @return array<string,string>
	 */
	public static function create_pairing_token( $enrollment_id, $dashboard_origin, $expected_client_origin, $expires_at ) {
		$secret = self::create_secret();

		return array(
			'token'       => self::format_token( $enrollment_id, $dashboard_origin, $expected_client_origin, $secret, $expires_at ),
			'secret_hash' => self::hash_secret( $secret ),
			'expires_at'  => gmdate( 'c', (int) $expires_at ),
		);
	}

	/**
	 * Hashes a secret for verifier storage.
	 *
	 * @since 0.1.0
	 *
	 * @param string $secret Plain secret.
	 * @return string
	 */
	public static function hash_secret( $secret ) {
		return hash( 'sha256', (string) $secret );
	}

	/**
	 * Base64url encodes binary or JSON data without padding.
	 *
	 * @param string $value Raw value.
	 * @return string
	 */
	private static function base64url_encode( $value ) {
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Transport encoding for opaque pairing tokens, not code obfuscation.
		return rtrim( strtr( base64_encode( $value ), '+/', '-_' ), '=' );
	}
}
