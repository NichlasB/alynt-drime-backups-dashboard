<?php
/**
 * Poller site row fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Builds dashboard site rows for poller tests.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Poller_Site_Row_Fixtures {
	/**
	 * Creates a dashboard site row.
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Credential_Vault $vault Vault.
	 * @param array<string,mixed>                            $overrides Site row overrides.
	 * @return array<string,mixed>
	 */
	private function site( $vault, $overrides = array() ) {
		$public_id = isset( $overrides['public_id'] ) ? (string) $overrides['public_id'] : '00000000-0000-4000-8000-000000000000';
		$site      = array(
			'id'                         => 77,
			'public_id'                  => $public_id,
			'expected_origin'            => 'https://client.example.com',
			'site_uuid'                  => '11111111-1111-4111-8111-111111111111',
			'polling_key_id'             => 'pk_example_0000000000000000',
			'polling_secret_ciphertext'  => $vault->encrypt( str_repeat( 'S', 43 ), 'site:' . $public_id ),
			'enrollment_status'          => 'awaiting_first_poll',
			'overall_status'             => 'pending',
			'consecutive_failures'       => 0,
		);

		return array_merge( $site, $overrides );
	}
}
