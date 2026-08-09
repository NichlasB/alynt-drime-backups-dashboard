<?php
/**
 * Pairing token helper tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

if ( ! function_exists( 'wp_json_encode' ) ) {
	/**
	 * Minimal test shim.
	 *
	 * @param mixed $value Value to encode.
	 * @return string|false
	 */
	function wp_json_encode( $value ) {
		return json_encode( $value );
	}
}

require_once dirname( __DIR__ ) . '/includes/class-pairing-tokens.php';

/**
 * Tests pairing token primitives.
 */
class PairingTokensTest extends TestCase {
	/**
	 * Token formatting includes the expected version prefix.
	 *
	 * @return void
	 */
	public function test_format_token_uses_version_prefix() {
		$token = Alynt_Drime_Backups_Dashboard_Pairing_Tokens::format_token(
			'enrollment-1',
			'https://control.sitesmanage.com',
			'secret',
			1234567890
		);

		$this->assertStringStartsWith( 'adb1.', $token );
	}

	/**
	 * Secrets are generated with URL-safe characters.
	 *
	 * @return void
	 */
	public function test_create_secret_is_url_safe() {
		$secret = Alynt_Drime_Backups_Dashboard_Pairing_Tokens::create_secret();

		$this->assertMatchesRegularExpression( '/^[A-Za-z0-9_-]+$/', $secret );
	}
}
