<?php
/**
 * Remote action schedule apply rollback metadata alias fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared schedule apply rollback metadata alias fixture builders.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Schedule_Apply_Rollback_Metadata_Fixtures {
	/**
	 * Builds schedule-apply rollback metadata aliases.
	 *
	 * @return array<string,mixed>
	 */
	private function schedule_apply_rollback_metadata() {
		return array(
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
		);
	}
}
