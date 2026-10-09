<?php
/**
 * Remote action rollback capability test fixture builders.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/remote-action-capabilities-rollback-last-action-fixtures.php';

/**
 * Shared rollback fixture builders for remote action capability tests.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Rollback_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Rollback_Last_Action_Fixtures;

	/**
	 * Builds a schedule rollback-preview capability summary for sanitizer coverage.
	 *
	 * @return array<string,mixed>
	 */
	private function schedule_rollback_preview_capability_summary() {
		return array(
			'protocol_version'    => 2,
			'enabled'             => true,
			'sodium_available'    => true,
			'allowed_actions'     => array( 'scan_upload_now', 'schedule_preview', 'schedule_rollback_preview' ),
			'schedule_management' => array(
				'protocol_version'           => 2,
				'capability_version'         => 1,
				'enabled'                    => true,
				'preview_only'               => true,
				'apply_supported'            => false,
				'rollback_preview_supported' => true,
				'rollback_supported'         => false,
				'schedules'                  => array(
					$this->alynt_scan_upload_schedule(
						array(
							'current_cadence'            => 'every_30_minutes',
							'supported_cadences'         => array( 'every_15_minutes', 'every_30_minutes' ),
							'rollback_preview_supported' => true,
							'rollback_supported'         => true,
						)
					),
				),
			),
			'last_action'         => $this->schedule_rollback_preview_last_action_summary(),
		);
	}
}
