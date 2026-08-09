<?php
/**
 * Pairing token helper tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

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
			'00000000-0000-4000-8000-000000000000',
			'https://control.sitesmanage.com',
			'https://client.example.com',
			str_repeat( 'A', 43 ),
			strtotime( '2099-01-01T00:00:00Z' )
		);

		$this->assertStringStartsWith( 'adb1.', $token );
	}

	/**
	 * Token formatting matches the frozen protocol v1 payload shape.
	 *
	 * @return void
	 */
	public function test_format_token_uses_protocol_v1_payload_shape() {
		$token   = Alynt_Drime_Backups_Dashboard_Pairing_Tokens::format_token(
			'00000000-0000-4000-8000-000000000000',
			'https://control.sitesmanage.com',
			'https://client.example.com',
			str_repeat( 'A', 43 ),
			strtotime( '2099-01-01T00:00:00Z' )
		);
		$payload = $this->decode_token( $token );

		$this->assertSame( 1, $payload['protocol_version'] );
		$this->assertSame( '00000000-0000-4000-8000-000000000000', $payload['enrollment_id'] );
		$this->assertSame( 'https://control.sitesmanage.com', $payload['dashboard_origin'] );
		$this->assertSame( 'https://client.example.com', $payload['expected_client_origin'] );
		$this->assertSame( str_repeat( 'A', 43 ), $payload['secret'] );
		$this->assertSame( '2099-01-01T00:00:00+00:00', $payload['expires_at'] );
		$this->assertArrayNotHasKey( 'version', $payload );
	}

	/**
	 * Created token material stores only the verifier beside the display token.
	 *
	 * @return void
	 */
	public function test_create_pairing_token_returns_verifier_without_plain_secret_field() {
		$material = Alynt_Drime_Backups_Dashboard_Pairing_Tokens::create_pairing_token(
			'00000000-0000-4000-8000-000000000000',
			'https://control.sitesmanage.com',
			'https://client.example.com',
			strtotime( '2099-01-01T00:00:00Z' )
		);

		$this->assertArrayHasKey( 'token', $material );
		$this->assertArrayHasKey( 'secret_hash', $material );
		$this->assertArrayNotHasKey( 'secret', $material );
		$this->assertMatchesRegularExpression( '/^[a-f0-9]{64}$/', $material['secret_hash'] );
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

	/**
	 * Decodes a test token payload.
	 *
	 * @param string $token Token.
	 * @return array<string,mixed>
	 */
	private function decode_token( $token ) {
		$encoded = substr( $token, strlen( Alynt_Drime_Backups_Dashboard_Pairing_Tokens::TOKEN_PREFIX ) );
		$padded  = $encoded . str_repeat( '=', ( 4 - strlen( $encoded ) % 4 ) % 4 );
		$json    = base64_decode( strtr( $padded, '-_', '+/' ) );
		$payload = json_decode( $json, true );

		return is_array( $payload ) ? $payload : array();
	}
}
