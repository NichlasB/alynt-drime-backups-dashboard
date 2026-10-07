<?php
/**
 * Test support for remote action dispatcher tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/remote-action-dispatcher-test-doubles.php';
require_once __DIR__ . '/remote-action-dispatcher-snapshot-fixtures.php';

/**
 * Shared fixture builders for remote action dispatcher tests.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_Test_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_Snapshot_Fixtures;

	/**
	 * Creates dispatcher.
	 *
	 * @param callable|null                                 $http HTTP fake.
	 * @param callable|null                                 $resolver DNS resolver fake.
	 * @param Alynt_Drime_Backups_Dashboard_Remote_Action_Repository|null $actions Actions repository.
	 * @return Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher
	 */
	private function dispatcher( $http = null, $resolver = null, $actions = null ) {
		return new Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher(
			new Alynt_Drime_Backups_Dashboard_Site_Repository(),
			new Alynt_Drime_Backups_Dashboard_Snapshot_Repository(),
			$actions instanceof Alynt_Drime_Backups_Dashboard_Remote_Action_Repository ? $actions : new Alynt_Drime_Backups_Dashboard_Remote_Action_Repository(),
			new Alynt_Drime_Backups_Dashboard_Origin_Validator(),
			new Alynt_Drime_Backups_Dashboard_Test_Dispatcher_Vault(),
			new Alynt_Drime_Backups_Dashboard_Test_Dispatcher_Signer(),
			new Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities(),
			$http,
			null === $resolver ? function () {
				return array( '93.184.216.34' );
			} : $resolver
		);
	}

	/**
	 * Site fixture.
	 *
	 * @param array<string,mixed> $overrides Overrides.
	 * @return array<string,mixed>
	 */
	private function site_row( array $overrides = array() ) {
		return array_merge(
			array(
				'id'                            => 9,
				'public_id'                     => '00000000-0000-4000-8000-000000000000',
				'site_uuid'                     => '11111111-1111-4111-8111-111111111111',
				'expected_origin'               => 'https://client.example.com',
				'enrollment_status'             => 'active',
				'polling_key_id'                => 'pk_test',
				'polling_secret_ciphertext'     => 'poll-cipher',
				'action_key_id'                 => 'ak_test',
				'action_private_key_ciphertext' => 'action-cipher',
			),
			$overrides
		);
	}
}
