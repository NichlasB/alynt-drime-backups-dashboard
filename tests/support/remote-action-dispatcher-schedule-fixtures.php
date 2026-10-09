<?php
/**
 * Schedule fixtures for remote action dispatcher tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared schedule-management payload builders for remote action dispatcher tests.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_Schedule_Fixtures {
	/**
	 * Builds a schedule-management capability payload.
	 *
	 * @param array<string,mixed> $remote_actions Remote-action values.
	 * @return array<string,mixed>
	 */
	private function dispatcher_schedule_management( array $remote_actions ) {
		return array(
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
		);
	}
}
