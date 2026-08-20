<?php
/**
 * Remote action signing helper.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.15
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates and verifies deterministic V2 remote-action signatures.
 *
 * @since 0.1.15
 */
class Alynt_Drime_Backups_Dashboard_Remote_Action_Signer {
	const SIGNING_PREFIX    = 'ADB-ACTION-V2';
	const ACTION_KEY_PREFIX = 'ak_';

	/**
	 * Returns whether Ed25519 signing is available.
	 *
	 * @since 0.1.15
	 *
	 * @return bool
	 */
	public function is_supported() {
		return function_exists( 'sodium_crypto_sign_keypair' )
			&& function_exists( 'sodium_crypto_sign_detached' )
			&& function_exists( 'sodium_crypto_sign_verify_detached' );
	}

	/**
	 * Creates a new action key pair.
	 *
	 * @since 0.1.15
	 *
	 * @return array<string,string>|WP_Error
	 */
	public function create_key_pair() {
		if ( ! $this->is_supported() ) {
			return new WP_Error( 'action_signing_unavailable', __( 'Remote action signing is unavailable because PHP Sodium support is missing.', 'alynt-drime-backups-dashboard' ) );
		}

		$key_pair = sodium_crypto_sign_keypair();

		return array(
			'key_id'      => $this->create_key_id(),
			'public_key'  => $this->base64url_encode( sodium_crypto_sign_publickey( $key_pair ) ),
			'private_key' => $this->base64url_encode( sodium_crypto_sign_secretkey( $key_pair ) ),
		);
	}

	/**
	 * Builds deterministic JSON for signing.
	 *
	 * @since 0.1.15
	 *
	 * @param array<string,mixed> $body Body.
	 * @return string|WP_Error
	 */
	public function canonical_json( array $body ) {
		$body    = $this->sort_recursive( $body );
		$encoded = wp_json_encode( $body, JSON_UNESCAPED_SLASHES );

		if ( false === $encoded ) {
			return new WP_Error( 'action_body_encode_failed', __( 'The remote action body could not be encoded for signing.', 'alynt-drime-backups-dashboard' ) );
		}

		return (string) $encoded;
	}

	/**
	 * Builds the V2 signing input.
	 *
	 * @since 0.1.15
	 *
	 * @param string $method HTTP method.
	 * @param string $route Route path.
	 * @param string $origin Canonical client origin.
	 * @param string $body_json Canonical JSON body.
	 * @param string $signed_at Signed-at ISO timestamp.
	 * @return string
	 */
	public function signing_input( $method, $route, $origin, $body_json, $signed_at ) {
		return implode(
			"\n",
			array(
				self::SIGNING_PREFIX,
				strtoupper( sanitize_key( (string) $method ) ),
				'/' . ltrim( (string) $route, '/' ),
				rtrim( strtolower( (string) $origin ), '/' ),
				hash( 'sha256', (string) $body_json ),
				sanitize_text_field( (string) $signed_at ),
			)
		);
	}

	/**
	 * Signs canonical input.
	 *
	 * @since 0.1.15
	 *
	 * @param string $private_key Encoded private key.
	 * @param string $signing_input Signing input.
	 * @return string|WP_Error
	 */
	public function sign( $private_key, $signing_input ) {
		if ( ! $this->is_supported() ) {
			return new WP_Error( 'action_signing_unavailable', __( 'Remote action signing is unavailable because PHP Sodium support is missing.', 'alynt-drime-backups-dashboard' ) );
		}

		$decoded = $this->base64url_decode( $private_key );

		if ( false === $decoded || SODIUM_CRYPTO_SIGN_SECRETKEYBYTES !== strlen( $decoded ) ) {
			return new WP_Error( 'action_private_key_invalid', __( 'The remote action private key is invalid.', 'alynt-drime-backups-dashboard' ) );
		}

		return $this->base64url_encode( sodium_crypto_sign_detached( (string) $signing_input, $decoded ) );
	}

	/**
	 * Verifies a signed input.
	 *
	 * @since 0.1.15
	 *
	 * @param string $public_key Encoded public key.
	 * @param string $signing_input Signing input.
	 * @param string $signature Encoded signature.
	 * @return bool
	 */
	public function verify( $public_key, $signing_input, $signature ) {
		if ( ! $this->is_supported() ) {
			return false;
		}

		$decoded_public_key = $this->base64url_decode( $public_key );
		$decoded_signature  = $this->base64url_decode( $signature );

		if (
			false === $decoded_public_key
			|| false === $decoded_signature
			|| SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES !== strlen( $decoded_public_key )
			|| SODIUM_CRYPTO_SIGN_BYTES !== strlen( $decoded_signature )
		) {
			return false;
		}

		return sodium_crypto_sign_verify_detached( $decoded_signature, (string) $signing_input, $decoded_public_key );
	}

	/**
	 * Encodes bytes as base64url.
	 *
	 * @since 0.1.15
	 *
	 * @param string $value Bytes.
	 * @return string
	 */
	public function base64url_encode( $value ) {
		return rtrim( strtr( base64_encode( (string) $value ), '+/', '-_' ), '=' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Transport encoding for keys/signatures.
	}

	/**
	 * Decodes base64url.
	 *
	 * @since 0.1.15
	 *
	 * @param string $value Encoded value.
	 * @return string|false
	 */
	public function base64url_decode( $value ) {
		$value = (string) $value;

		if ( '' === $value || ! preg_match( '/^[A-Za-z0-9_-]+$/', $value ) ) {
			return false;
		}

		$pad = strlen( $value ) % 4;

		if ( $pad ) {
			$value .= str_repeat( '=', 4 - $pad );
		}

		return base64_decode( strtr( $value, '-_', '+/' ), true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- Transport decoding for keys/signatures.
	}

	/**
	 * Creates a public key ID.
	 *
	 * @return string
	 */
	private function create_key_id() {
		return self::ACTION_KEY_PREFIX . $this->base64url_encode( random_bytes( 16 ) );
	}

	/**
	 * Recursively sorts array keys for deterministic JSON.
	 *
	 * @param array<string,mixed> $value Value.
	 * @return array<string,mixed>
	 */
	private function sort_recursive( array $value ) {
		foreach ( $value as $key => $child ) {
			if ( is_array( $child ) ) {
				$value[ $key ] = $this->sort_recursive( $child );
			}
		}

		ksort( $value );

		return $value;
	}
}
