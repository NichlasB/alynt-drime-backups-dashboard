<?php
/**
 * Remote action opt-in manager tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

/**
 * Test signer with deterministic Sodium-free key material.
 */
class Alynt_Drime_Backups_Dashboard_Test_Action_Opt_In_Signer extends Alynt_Drime_Backups_Dashboard_Remote_Action_Signer {
	/**
	 * Whether the signer is supported.
	 *
	 * @return bool
	 */
	public function is_supported() {
		return true;
	}

	/**
	 * Creates deterministic key material.
	 *
	 * @return array<string,string>
	 */
	public function create_key_pair() {
		return array(
			'key_id'      => 'ak_test',
			'public_key'  => str_repeat( 'A', 43 ),
			'private_key' => str_repeat( 'B', 86 ),
		);
	}
}

/**
 * Test site repository for opt-in manager behavior.
 */
class Alynt_Drime_Backups_Dashboard_Test_Action_Opt_In_Site_Repository extends Alynt_Drime_Backups_Dashboard_Site_Repository {
	/**
	 * Site row.
	 *
	 * @var array<string,mixed>|null
	 */
	public $site;

	/**
	 * Stored action key ID.
	 *
	 * @var string
	 */
	public $stored_key_id = '';

	/**
	 * Stored ciphertext.
	 *
	 * @var string
	 */
	public $stored_ciphertext = '';

	/**
	 * Constructor.
	 *
	 * @param array<string,mixed>|null $site Site row.
	 */
	public function __construct( $site ) {
		$this->site = $site;
	}

	/**
	 * Returns the test site.
	 *
	 * @param int $site_id Site ID.
	 * @return array<string,mixed>|null
	 */
	public function get( $site_id ) {
		return 7 === (int) $site_id ? $this->site : null;
	}

	/**
	 * Stores action key material.
	 *
	 * @param int    $site_id Site ID.
	 * @param string $action_key_id Action key ID.
	 * @param string $private_key_ciphertext Encrypted private key.
	 * @return bool
	 */
	public function store_action_signing_key( $site_id, $action_key_id, $private_key_ciphertext ) {
		if ( 7 !== (int) $site_id ) {
			return false;
		}

		$this->stored_key_id     = (string) $action_key_id;
		$this->stored_ciphertext = (string) $private_key_ciphertext;

		return true;
	}
}

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
