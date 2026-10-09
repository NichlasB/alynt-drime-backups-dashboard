<?php
/**
 * Remote action rollback latest-action capability fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared rollback latest-action fixture builders for remote action capability tests.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Rollback_Last_Action_Fixtures {
	/**
	 * Builds a schedule rollback-preview latest-action summary.
	 *
	 * @return array<string,mixed>
	 */
	private function schedule_rollback_preview_last_action_summary() {
		return array(
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
		);
	}
}
