<?php
/**
 * Remote action opt-in token tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

/**
 * Tests dashboard-generated V2 action opt-in tokens.
 */
class RemoteActionOptInTokensTest extends TestCase {
	/**
	 * Tokens are prefixed opaque JSON envelopes and never include private key material.
	 *
	 * @return void
	 */
	public function test_format_token_contains_expected_public_opt_in_fields_without_private_key() {
		$token = Alynt_Drime_Backups_Dashboard_Remote_Action_Opt_In_Tokens::format_token(
			array(
				'dashboard_origin'         => 'https://control.sitesmanage.com',
				'expected_client_origin'   => 'https://client.example.com',
				'dashboard_site_public_id' => '00000000-0000-4000-8000-000000000000',
				'site_uuid'                => '11111111-1111-4111-8111-111111111111',
				'action_key_id'            => 'ak_test',
				'action_public_key'        => str_repeat( 'A', 43 ),
				'expires_at'               => '2099-01-01T00:00:00+00:00',
			)
		);

		$this->assertIsString( $token );
		$this->assertStringStartsWith( Alynt_Drime_Backups_Dashboard_Remote_Action_Opt_In_Tokens::TOKEN_PREFIX, $token );

		$encoded = substr( $token, strlen( Alynt_Drime_Backups_Dashboard_Remote_Action_Opt_In_Tokens::TOKEN_PREFIX ) );
		$payload = json_decode( $this->base64url_decode( $encoded ), true );

		$this->assertIsArray( $payload );
		$this->assertSame( 2, $payload['protocol_version'] );
		$this->assertSame( 'remote_action_opt_in', $payload['purpose'] );
		$this->assertSame( 'https://control.sitesmanage.com', $payload['dashboard_origin'] );
		$this->assertSame( 'https://client.example.com', $payload['expected_client_origin'] );
		$this->assertSame( array( 'scan_upload_now' ), $payload['allowed_actions'] );
		$this->assertArrayHasKey( 'action_public_key', $payload );
		$this->assertArrayNotHasKey( 'action_private_key', $payload );
		$this->assertStringNotContainsString( 'private', strtolower( wp_json_encode( $payload ) ) );
	}

	/**
	 * Decodes base64url text.
	 *
	 * @param string $value Encoded value.
	 * @return string
	 */
	private function base64url_decode( $value ) {
		$padded = (string) $value;
		$pad    = strlen( $padded ) % 4;

		if ( $pad ) {
			$padded .= str_repeat( '=', 4 - $pad );
		}

		return base64_decode( strtr( $padded, '-_', '+/' ), true );
	}
}
