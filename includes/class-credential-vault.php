<?php
/**
 * Dashboard credential vault.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Encrypts dashboard-owned polling credentials at rest.
 *
 * The derived key is never persisted. Decryption failures fail closed and
 * require re-pairing when WordPress secret material changes.
 *
 * @since 0.1.0
 */
class Alynt_Drime_Backups_Dashboard_Credential_Vault {
	const CIPHERTEXT_PREFIX = 'adbv1.';
	const CIPHER            = 'aes-256-gcm';
	const KEY_CONTEXT       = 'alynt-drime-backups-dashboard credential vault v1';

	/**
	 * Secret material override for tests.
	 *
	 * @var string
	 */
	private $secret_material;

	/**
	 * Constructor.
	 *
	 * @param string|null $secret_material Secret material override.
	 */
	public function __construct( $secret_material = null ) {
		$this->secret_material = null === $secret_material ? '' : (string) $secret_material;
	}

	/**
	 * Encrypts a polling credential for storage.
	 *
	 * @param string $secret Plain polling secret.
	 * @param string $context Credential context.
	 * @return string|WP_Error
	 */
	public function encrypt( $secret, $context = 'polling' ) {
		$key = $this->derive_key( $context );

		if ( is_wp_error( $key ) ) {
			return $key;
		}

		try {
			$iv = random_bytes( 12 );
		} catch ( Exception $exception ) {
			return new WP_Error( 'credential_vault_unavailable', __( 'Secure random bytes are not available for the dashboard credential vault.', 'alynt-drime-backups-dashboard' ) );
		}

		$tag        = '';
		$aad        = $this->aad( $context );
		$ciphertext = openssl_encrypt( (string) $secret, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv, $tag, $aad, 16 );

		if ( false === $ciphertext || '' === $tag ) {
			return new WP_Error( 'credential_encrypt_failed', __( 'The dashboard credential could not be encrypted.', 'alynt-drime-backups-dashboard' ) );
		}

		$payload = array(
			'v'       => 1,
			'cipher'  => self::CIPHER,
			'context' => (string) $context,
			'iv'      => $this->base64url_encode( $iv ),
			'tag'     => $this->base64url_encode( $tag ),
			'ct'      => $this->base64url_encode( $ciphertext ),
		);

		return self::CIPHERTEXT_PREFIX . $this->base64url_encode( wp_json_encode( $payload ) );
	}

	/**
	 * Decrypts a stored polling credential.
	 *
	 * @param string $stored Stored ciphertext.
	 * @param string $context Credential context.
	 * @return string|WP_Error
	 */
	public function decrypt( $stored, $context = 'polling' ) {
		$payload = $this->decode_payload( $stored );

		if ( is_wp_error( $payload ) ) {
			return $payload;
		}

		if ( 1 !== (int) $payload['v'] || self::CIPHER !== (string) $payload['cipher'] || ! hash_equals( (string) $payload['context'], (string) $context ) ) {
			return new WP_Error( 'credential_ciphertext_invalid', __( 'The stored dashboard credential is not valid for this context.', 'alynt-drime-backups-dashboard' ) );
		}

		$key = $this->derive_key( $context );

		if ( is_wp_error( $key ) ) {
			return $key;
		}

		$iv         = $this->base64url_decode( (string) $payload['iv'] );
		$tag        = $this->base64url_decode( (string) $payload['tag'] );
		$ciphertext = $this->base64url_decode( (string) $payload['ct'] );

		if ( false === $iv || false === $tag || false === $ciphertext ) {
			return new WP_Error( 'credential_ciphertext_invalid', __( 'The stored dashboard credential could not be decoded.', 'alynt-drime-backups-dashboard' ) );
		}

		$plaintext = openssl_decrypt( $ciphertext, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv, $tag, $this->aad( $context ) );

		if ( false === $plaintext ) {
			return new WP_Error( 'credential_decrypt_failed', __( 'The dashboard credential could not be decrypted and must be re-paired.', 'alynt-drime-backups-dashboard' ) );
		}

		return $plaintext;
	}

	/**
	 * Derives a local encryption key from WordPress secret material.
	 *
	 * @param string $context Credential context.
	 * @return string|WP_Error
	 */
	private function derive_key( $context ) {
		if ( ! function_exists( 'openssl_encrypt' ) || ! function_exists( 'openssl_decrypt' ) ) {
			return new WP_Error( 'credential_vault_unavailable', __( 'OpenSSL encryption is not available for the dashboard credential vault.', 'alynt-drime-backups-dashboard' ) );
		}

		$material = '' === $this->secret_material ? $this->wordpress_secret_material() : $this->secret_material;

		if ( strlen( $material ) < 32 ) {
			return new WP_Error( 'credential_key_unavailable', __( 'WordPress secret material is not available for the dashboard credential vault.', 'alynt-drime-backups-dashboard' ) );
		}

		return hash_hkdf( 'sha256', $material, 32, self::KEY_CONTEXT . ':' . (string) $context, 'adb-dashboard-v1' );
	}

	/**
	 * Collects WordPress secret material without persisting it.
	 *
	 * @return string
	 */
	private function wordpress_secret_material() {
		$parts     = array();
		$constants = array(
			'AUTH_KEY',
			'SECURE_AUTH_KEY',
			'LOGGED_IN_KEY',
			'NONCE_KEY',
			'AUTH_SALT',
			'SECURE_AUTH_SALT',
			'LOGGED_IN_SALT',
			'NONCE_SALT',
		);

		foreach ( $constants as $constant ) {
			if ( defined( $constant ) ) {
				$value = (string) constant( $constant );

				if ( '' !== $value && false === strpos( $value, 'put your unique phrase here' ) ) {
					$parts[] = $value;
				}
			}
		}

		if ( function_exists( 'wp_salt' ) ) {
			$parts[] = (string) wp_salt( 'auth' );
			$parts[] = (string) wp_salt( 'secure_auth' );
		}

		return implode( '|', array_filter( $parts ) );
	}

	/**
	 * Decodes a stored ciphertext payload.
	 *
	 * @param string $stored Stored ciphertext.
	 * @return array<string,mixed>|WP_Error
	 */
	private function decode_payload( $stored ) {
		$stored = trim( (string) $stored );

		if ( 0 !== strpos( $stored, self::CIPHERTEXT_PREFIX ) ) {
			return new WP_Error( 'credential_ciphertext_invalid', __( 'The stored dashboard credential is not a supported vault payload.', 'alynt-drime-backups-dashboard' ) );
		}

		$json = $this->base64url_decode( substr( $stored, strlen( self::CIPHERTEXT_PREFIX ) ) );

		if ( false === $json ) {
			return new WP_Error( 'credential_ciphertext_invalid', __( 'The stored dashboard credential payload could not be decoded.', 'alynt-drime-backups-dashboard' ) );
		}

		$payload = json_decode( $json, true );

		if ( ! is_array( $payload ) ) {
			return new WP_Error( 'credential_ciphertext_invalid', __( 'The stored dashboard credential payload is not JSON.', 'alynt-drime-backups-dashboard' ) );
		}

		foreach ( array( 'v', 'cipher', 'context', 'iv', 'tag', 'ct' ) as $key ) {
			if ( ! array_key_exists( $key, $payload ) ) {
				return new WP_Error( 'credential_ciphertext_invalid', __( 'The stored dashboard credential payload is incomplete.', 'alynt-drime-backups-dashboard' ) );
			}
		}

		return $payload;
	}

	/**
	 * Gets authenticated additional data for a credential context.
	 *
	 * @param string $context Credential context.
	 * @return string
	 */
	private function aad( $context ) {
		return 'alynt-drime-backups-dashboard:' . (string) $context . ':v1';
	}

	/**
	 * Base64url encodes bytes.
	 *
	 * @param string $value Raw value.
	 * @return string
	 */
	private function base64url_encode( $value ) {
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Transport encoding for encrypted vault payloads, not code obfuscation.
		return rtrim( strtr( base64_encode( $value ), '+/', '-_' ), '=' );
	}

	/**
	 * Base64url decodes bytes.
	 *
	 * @param string $value Encoded value.
	 * @return string|false
	 */
	private function base64url_decode( $value ) {
		$value = (string) $value;

		if ( '' === $value || ! preg_match( '/^[A-Za-z0-9_-]+$/', $value ) ) {
			return false;
		}

		$value .= str_repeat( '=', ( 4 - strlen( $value ) % 4 ) % 4 );

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- Transport decoding for encrypted vault payloads, not code obfuscation.
		return base64_decode( strtr( $value, '-_', '+/' ), true );
	}
}
