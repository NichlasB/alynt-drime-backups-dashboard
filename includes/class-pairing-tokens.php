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
	const TOKEN_PREFIX = 'adb1.';

	/**
	 * Creates a random URL-safe secret.
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
	 * @param string $enrollment_id Pending enrollment identifier.
	 * @param string $dashboard_origin Canonical dashboard origin.
	 * @param string $secret One-time enrollment secret.
	 * @param int    $expires_at Unix timestamp expiry.
	 * @return string
	 */
	public static function format_token( $enrollment_id, $dashboard_origin, $secret, $expires_at ) {
		$payload = array(
			'version'          => 1,
			'enrollment_id'    => (string) $enrollment_id,
			'dashboard_origin' => (string) $dashboard_origin,
			'secret'           => (string) $secret,
			'expires_at'       => (int) $expires_at,
		);

		return self::TOKEN_PREFIX . self::base64url_encode( wp_json_encode( $payload ) );
	}

	/**
	 * Hashes a secret for verifier storage.
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
