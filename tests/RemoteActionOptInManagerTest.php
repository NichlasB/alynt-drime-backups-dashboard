<?php
/**
 * Remote action opt-in manager tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/remote-action-opt-in-manager-test-doubles.php';

/**
 * Tests remote action opt-in token creation.
 */
class RemoteActionOptInManagerTest extends TestCase {
	/**
	 * Opt-in generation stores encrypted private key and returns display-once public token.
	 *
	 * @return void
	 */
	public function test_create_opt_in_token_stores_private_key_and_returns_public_token() {
		$sites   = new Alynt_Drime_Backups_Dashboard_Test_Action_Opt_In_Site_Repository(
			array(
				'id'                        => 7,
				'expected_origin'           => 'https://client.example.com',
				'public_id'                 => '00000000-0000-4000-8000-000000000000',
				'site_uuid'                 => '11111111-1111-4111-8111-111111111111',
				'polling_key_id'            => 'pk_test',
				'polling_secret_ciphertext' => 'encrypted_polling_secret',
			)
		);
		$manager = new Alynt_Drime_Backups_Dashboard_Remote_Action_Opt_In_Manager(
			$sites,
			new Alynt_Drime_Backups_Dashboard_Origin_Validator(),
			new Alynt_Drime_Backups_Dashboard_Credential_Vault( str_repeat( 'S', 64 ) ),
			new Alynt_Drime_Backups_Dashboard_Test_Action_Opt_In_Signer()
		);

		$result = $manager->create_opt_in_token( 7, 'https://control.sitesmanage.com', strtotime( '2026-08-20T12:00:00Z' ) );

		$this->assertIsArray( $result );
		$this->assertSame( 'generate_action_opt_in_token', $result['action'] );
		$this->assertSame( 'ak_test', $result['action_key_id'] );
		$this->assertSame( 'ak_test', $sites->stored_key_id );
		$this->assertNotSame( '', $sites->stored_ciphertext );
		$this->assertStringStartsWith( 'adb2a.', $result['action_opt_in_token'] );
		$this->assertStringNotContainsString( str_repeat( 'B', 86 ), $result['action_opt_in_token'] );
	}
}
