<?php
/**
 * Remote action opt-in manager test doubles.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

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
