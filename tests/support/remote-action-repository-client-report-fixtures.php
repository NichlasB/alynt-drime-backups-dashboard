<?php
/**
 * Remote action repository client-report fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared client-report payload fixtures for remote action repository tests.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Client_Report_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Client_Report_Rollback_Fixtures;

	/**
	 * Returns a support-safe schedule-apply client report.
	 *
	 * @return array<string,mixed>
	 */
	private function schedule_apply_client_report() {
		return array(
			'state'          => 'succeeded',
			'result_code'    => 'schedule_apply_succeeded',
			'result_summary' => 'Schedule apply completed for Alynt scan/upload.',
			'schedule_apply' => array(
				'schedule_id'          => 'alynt_scan_upload',
				'label'                => 'Alynt scan/upload',
				'owner'                => 'alynt_uploader',
				'capability_version'   => 1,
				'preview_action_id'    => '22222222-2222-4222-8222-222222222222',
				'preview_fingerprint'  => str_repeat( 'a', 64 ),
				'proposed_cadence'     => 'every_30_minutes',
				'previous_cadence'     => 'every_15_minutes',
				'applied_cadence'      => 'every_30_minutes',
				'previous_next_run_at' => '2026-09-15T18:30:03+00:00',
				'applied_next_run_at'  => '2026-09-15T18:53:55+00:00',
				'changed'              => true,
				'rollback_available'   => true,
				'rollback_metadata'    => array(
					'captured'                            => true,
					'available'                           => true,
					'reason'                              => 'schedule_rollback_runtime_not_implemented',
					'source_action_id'                    => '33333333-3333-4333-8333-333333333333',
					'source_preview_action_id'            => '22222222-2222-4222-8222-222222222222',
					'schedule_id'                         => 'alynt_scan_upload',
					'owner'                               => 'alynt_uploader',
					'previous_cadence'                    => 'every_15_minutes',
					'applied_cadence'                     => 'every_30_minutes',
					'previous_next_run_at'                => '2026-09-15T18:30:03+00:00',
					'applied_next_run_at'                 => '2026-09-15T18:53:55+00:00',
					'current_schedule_fingerprint_before' => str_repeat( 'b', 64 ),
					'current_schedule_fingerprint_after'  => str_repeat( 'c', 64 ),
					'rollback_metadata_fingerprint'       => str_repeat( 'd', 64 ),
					'captured_at'                         => '2026-09-15T18:24:12+00:00',
					'expires_at'                          => '2026-09-15T19:24:12+00:00',
				),
			),
		);
	}
}
