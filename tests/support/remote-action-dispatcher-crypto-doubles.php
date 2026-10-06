<?php
/**
 * Fake crypto collaborators for remote action dispatcher tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Test vault.
 */
class Alynt_Drime_Backups_Dashboard_Test_Dispatcher_Vault extends Alynt_Drime_Backups_Dashboard_Credential_Vault {
	/**
	 * Decrypts fixed private key.
	 *
	 * @param string $stored Stored.
	 * @param string $context Context.
	 * @return string
	 */
	public function decrypt( $stored, $context = 'polling' ) {
		unset( $stored, $context );
		return 'private-key';
	}
}

/**
 * Test signer.
 */
class Alynt_Drime_Backups_Dashboard_Test_Dispatcher_Signer extends Alynt_Drime_Backups_Dashboard_Remote_Action_Signer {
	/**
	 * Whether supported.
	 *
	 * @return bool
	 */
	public function is_supported() {
		return true;
	}

	/**
	 * Encodes canonical JSON.
	 *
	 * @param array<string,mixed> $body Body.
	 * @return string
	 */
	public function canonical_json( array $body ) {
		ksort( $body );
		return wp_json_encode( $body, JSON_UNESCAPED_SLASHES );
	}

	/**
	 * Signs input.
	 *
	 * @param string $private_key Private key.
	 * @param string $signing_input Signing input.
	 * @return string
	 */
	public function sign( $private_key, $signing_input ) {
		unset( $private_key );
		return 'sig_' . hash( 'sha256', $signing_input );
	}
}
