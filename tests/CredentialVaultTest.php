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
		$vault  = $this->vault();
		$secret = $this->polling_secret( 'A' );

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
		$vault  = $this->vault();
		$stored = $vault->encrypt( $this->polling_secret( 'B' ) );

		$other  = $this->vault( 'z' );
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
		$result = $vault->encrypt( $this->polling_secret( 'C' ) );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'credential_key_unavailable', $result->get_error_code() );
	}

	/**
	 * Ciphertext context is authenticated.
	 *
	 * @return void
	 */
	public function test_context_mismatch_fails_closed() {
		$vault  = $this->vault();
		$stored = $vault->encrypt( $this->polling_secret( 'D' ), 'polling' );

		$result = $vault->decrypt( $stored, 'other' );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'credential_ciphertext_invalid', $result->get_error_code() );
	}

	/**
	 * Creates a deterministic vault.
	 *
	 * @param string $material Repeated key material character.
	 * @return Alynt_Drime_Backups_Dashboard_Credential_Vault
	 */
	private function vault( $material = 'k' ) {
		return new Alynt_Drime_Backups_Dashboard_Credential_Vault( str_repeat( $material, 64 ) );
	}

	/**
	 * Creates a deterministic polling secret.
	 *
	 * @param string $material Repeated secret material character.
	 * @return string
	 */
	private function polling_secret( $material ) {
		return 'polling-secret-' . str_repeat( $material, 32 );
	}
}
