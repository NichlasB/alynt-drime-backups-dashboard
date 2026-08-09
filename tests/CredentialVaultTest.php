<?php
/**
 * Credential vault tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/includes/class-credential-vault.php';

/**
 * Tests credential vault encryption behavior.
 */
class CredentialVaultTest extends TestCase {
	/**
	 * Vault encrypts and decrypts polling credentials.
	 *
	 * @return void
	 */
	public function test_encrypt_decrypt_round_trip_does_not_store_plaintext() {
		$vault  = new Alynt_Drime_Backups_Dashboard_Credential_Vault( str_repeat( 'k', 64 ) );
		$secret = 'polling-secret-' . str_repeat( 'A', 32 );

		$stored = $vault->encrypt( $secret );

		$this->assertIsString( $stored );
		$this->assertStringStartsWith( 'adbv1.', $stored );
		$this->assertStringNotContainsString( $secret, $stored );
		$this->assertSame( $secret, $vault->decrypt( $stored ) );
	}

	/**
	 * Vault fails closed when secret material changes.
	 *
	 * @return void
	 */
	public function test_decrypt_fails_closed_when_key_material_changes() {
		$vault  = new Alynt_Drime_Backups_Dashboard_Credential_Vault( str_repeat( 'k', 64 ) );
		$stored = $vault->encrypt( 'polling-secret-' . str_repeat( 'B', 32 ) );

		$other  = new Alynt_Drime_Backups_Dashboard_Credential_Vault( str_repeat( 'z', 64 ) );
		$result = $other->decrypt( $stored );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'credential_decrypt_failed', $result->get_error_code() );
	}

	/**
	 * Vault refuses to operate without enough secret material.
	 *
	 * @return void
	 */
	public function test_encrypt_requires_secret_material() {
		$vault  = new Alynt_Drime_Backups_Dashboard_Credential_Vault( 'short' );
		$result = $vault->encrypt( 'polling-secret-' . str_repeat( 'C', 32 ) );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'credential_key_unavailable', $result->get_error_code() );
	}

	/**
	 * Ciphertext context is authenticated.
	 *
	 * @return void
	 */
	public function test_context_mismatch_fails_closed() {
		$vault  = new Alynt_Drime_Backups_Dashboard_Credential_Vault( str_repeat( 'k', 64 ) );
		$stored = $vault->encrypt( 'polling-secret-' . str_repeat( 'D', 32 ), 'polling' );

		$result = $vault->decrypt( $stored, 'other' );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'credential_ciphertext_invalid', $result->get_error_code() );
	}
}
