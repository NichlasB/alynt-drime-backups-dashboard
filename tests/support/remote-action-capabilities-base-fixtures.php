<?php
/**
 * Base remote action capability fixture builders.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/remote-action-capabilities-last-action-fixtures.php';

/**
 * Shared base fixture builders for remote action capability tests.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Base_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Last_Action_Fixtures;

	/**
	 * Builds a representative valid remote-action capability summary.
	 *
	 * @return array<string,mixed>
	 */
	private function valid_remote_action_capability_summary() {
		return array(
			'protocol_version'             => 2,
			'enabled'                      => true,
			'key_id'                       => 'ak_valid.123',
			'allowed_actions'              => array(
				'scan_upload_now',
				'delete_backup',
				'scan_upload_now',
			),
			'sodium_available'             => true,
			'min_interval_seconds'         => 900,
			'one_running_action_per_site'  => true,
			'schedule_management'          => array(
				'protocol_version'   => 2,
				'capability_version' => 1,
				'enabled'            => true,
				'preview_only'       => true,
				'apply_supported'    => false,
				'rollback_supported' => false,
				'schedules'          => array(
					$this->alynt_scan_upload_schedule(
						array(
							'current_interval_seconds'       => 900,
							'current_next_run_at'            => '2026-06-25T16:45:00+00:00',
							'supported_cadences'             => array( 'every_15_minutes', 'every_15_minutes' ),
							'requires_high_friction_disable' => true,
							'rollback_supported'             => true,
							'extra_field'                    => 'ignored',
						)
					),
				),
			),
			'last_action'                  => $this->valid_remote_action_last_action_summary(),
			'extra_field'                  => 'ignored',
		);
	}
}
