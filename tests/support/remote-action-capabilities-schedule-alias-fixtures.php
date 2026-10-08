<?php
/**
 * Remote action schedule latest-action alias fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared schedule latest-action alias fixture builders.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Schedule_Alias_Fixtures {
	/**
	 * Builds a schedule-preview latest-action summary with code aliases.
	 *
	 * @return array<string,mixed>
	 */
	private function schedule_preview_alias_summary() {
		return array(
			'protocol_version' => 2,
			'enabled'          => true,
			'last_action'      => array(
				'action_id'        => '11111111-1111-4111-8111-111111111111',
				'action_type'      => 'schedule_preview',
				'state'            => 'succeeded',
				'code'             => 'schedule_preview_ready',
				'summary'          => 'Schedule preview is ready. No schedule was changed.',
				'schedule_preview' => array(
					'schedule_id'      => 'alynt_scan_upload',
					'current_cadence'  => 'every_15_minutes',
					'proposed_cadence' => 'every_30_minutes',
					'would_change'     => true,
				),
			),
		);
	}

	/**
	 * Builds a schedule-apply latest-action summary with next-run aliases.
	 *
	 * @return array<string,mixed>
	 */
	private function schedule_apply_alias_summary() {
		return array(
			'protocol_version' => 2,
			'enabled'          => true,
			'last_action'      => array(
				'action_id'      => '11111111-1111-4111-8111-111111111111',
				'action_type'    => 'schedule_apply',
				'state'          => 'succeeded',
				'code'           => 'schedule_apply_succeeded',
				'summary'        => 'Schedule apply completed for Alynt scan/upload.',
				'schedule_apply' => array(
					'schedule_id'          => 'alynt_scan_upload',
					'capability_version'   => 1,
					'preview_action_id'    => '22222222-2222-4222-8222-222222222222',
					'preview_fingerprint'  => str_repeat( 'a', 64 ),
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
						'source_action_id'                    => '11111111-1111-4111-8111-111111111111',
						'source_preview_action_id'            => '22222222-2222-4222-8222-222222222222',
						'schedule_id'                         => 'alynt_scan_upload',
						'owner'                               => 'alynt_uploader',
						'previous_cadence'                    => 'every_15_minutes',
						'applied_cadence'                     => 'every_30_minutes',
						'previous_next_run_at'                => '2026-09-15T18:30:03+00:00',
						'applied_next_run_at'                 => '2026-09-15T18:53:55+00:00',
						'current_schedule_fingerprint_before' => str_repeat( 'b', 64 ),
						'current_schedule_fingerprint_after'  => str_repeat( 'c', 64 ),
						'captured_at'                         => '2026-09-15T18:24:12+00:00',
						'expires_at'                          => '2026-09-15T19:24:12+00:00',
					),
				),
			),
		);
	}
}
