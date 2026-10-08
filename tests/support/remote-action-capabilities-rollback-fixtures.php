<?php
/**
 * Remote action rollback capability test fixture builders.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared rollback fixture builders for remote action capability tests.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Rollback_Fixtures {
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
			'last_action'         => array(
				'action_id'                 => '33333333-3333-4333-8333-333333333333',
				'action_type'               => 'schedule_rollback_preview',
				'state'                     => 'succeeded',
				'code'                      => 'schedule_rollback_preview_ready',
				'summary'                   => 'Schedule rollback preview is ready. No schedule was changed.',
				'schedule_rollback_preview' => array(
					'schedule_id'                           => 'alynt_scan_upload',
					'capability_version'                    => 1,
					'preview_action_id'                     => '33333333-3333-4333-8333-333333333333',
					'preview_fingerprint'                   => str_repeat( 'a', 64 ),
					'source_apply_action_id'                => '22222222-2222-4222-8222-222222222222',
					'rollback_metadata_fingerprint'         => str_repeat( 'd', 64 ),
					'current_cadence'                       => 'every_30_minutes',
					'applied_cadence'                       => 'every_30_minutes',
					'rollback_cadence'                      => 'every_15_minutes',
					'current_next_run_at'                   => '2026-09-15T18:53:55+00:00',
					'rollback_next_run_estimate_at'         => '2026-09-15T19:08:55+00:00',
					'current_schedule_fingerprint'          => str_repeat( 'c', 64 ),
					'expected_current_schedule_fingerprint' => str_repeat( 'c', 64 ),
					'previous_schedule_fingerprint'         => str_repeat( 'b', 64 ),
					'would_change'                          => true,
					'rollback_apply_supported'              => true,
					'rollback_supported'                    => true,
				),
			),
		);
	}
}
