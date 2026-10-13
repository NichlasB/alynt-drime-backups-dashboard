<?php
/**
 * Pairing token creation helper tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/includes/class-pairing-tokens.php';

/**
 * Tests generated pairing token material.
 */
class PairingTokenCreationTest extends TestCase {
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
}
