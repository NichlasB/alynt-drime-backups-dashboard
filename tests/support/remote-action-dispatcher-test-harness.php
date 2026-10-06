<?php
/**
 * Test support for remote action dispatcher tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/remote-action-dispatcher-test-doubles.php';

/**
 * Shared fixture builders for remote action dispatcher tests.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_Test_Fixtures {
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

	/**
	 * Snapshot fixture.
	 *
	 * @param array<string,mixed> $remote_action_overrides Remote-action overrides.
	 * @return array<string,mixed>
	 */
	private function snapshot_row( array $remote_action_overrides = array() ) {
		$remote_actions = array_merge(
			array(
				'protocol_version'            => 2,
				'enabled'                     => true,
				'key_id'                      => 'ak_test',
				'allowed_actions'             => array( 'scan_upload_now', 'schedule_preview' ),
				'sodium_available'            => true,
				'min_interval_seconds'        => 3600,
				'one_running_action_per_site' => true,
				'preview_only'                => true,
				'apply_supported'             => false,
				'rollback_supported'          => false,
				'rollback_preview_supported'  => false,
			),
			$remote_action_overrides
		);

		return array(
			'payload_json' => wp_json_encode(
				array(
					'remote_actions' => array(
						'protocol_version'            => $remote_actions['protocol_version'],
						'enabled'                     => $remote_actions['enabled'],
						'key_id'                      => $remote_actions['key_id'],
						'allowed_actions'             => $remote_actions['allowed_actions'],
						'sodium_available'            => $remote_actions['sodium_available'],
						'min_interval_seconds'        => $remote_actions['min_interval_seconds'],
						'one_running_action_per_site' => $remote_actions['one_running_action_per_site'],
						'schedule_management'         => array(
							'protocol_version'           => 2,
							'capability_version'         => 1,
							'enabled'                    => true,
							'preview_only'               => $remote_actions['preview_only'],
							'apply_supported'            => $remote_actions['apply_supported'],
							'rollback_preview_supported' => $remote_actions['rollback_preview_supported'],
							'rollback_supported'         => $remote_actions['rollback_supported'],
							'schedules'                  => array(
								array(
									'schedule_id'                    => 'alynt_scan_upload',
									'label'                          => 'Alynt scan/upload',
									'owner'                          => 'alynt_uploader',
									'manageable'                     => true,
									'current_cadence'                => 'every_15_minutes',
									'current_interval_seconds'       => 900,
									'current_next_run_at'            => '2026-06-25T16:45:00+00:00',
									'supported_cadences'             => array( 'every_15_minutes', 'every_30_minutes', 'hourly' ),
									'minimum_interval_seconds'       => 900,
									'can_disable'                    => false,
									'requires_high_friction_disable' => true,
									'rollback_preview_supported'     => $remote_actions['rollback_preview_supported'],
									'rollback_supported'             => false,
								),
							),
						),
					),
				)
			),
		);
	}
}
