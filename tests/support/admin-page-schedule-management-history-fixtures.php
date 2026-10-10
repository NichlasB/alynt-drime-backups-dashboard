<?php
/**
 * Admin schedule-management history fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared schedule-management history fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Schedule_Management_History_Fixtures {
	/**
	 * Builds a successful Schedule Apply row with rollback-preview metadata.
	 *
	 * @param string $public_id Public action ID.
	 * @return array<string,mixed>
	 */
	private function successful_schedule_apply_history_row( $public_id = '33333333-3333-4333-8333-333333333333' ) {
		return array(
			'public_id'             => $public_id,
			'action_type'           => 'schedule_apply',
			'state'                 => 'succeeded',
			'redacted_context_json' => wp_json_encode(
				array(
					'schedule_apply' => array(
						'schedule_id'       => 'alynt_scan_upload',
						'previous_cadence'  => 'every_15_minutes',
						'applied_cadence'   => 'every_30_minutes',
						'rollback_metadata' => array(
							'captured'                      => true,
							'schedule_id'                   => 'alynt_scan_upload',
							'rollback_metadata_fingerprint' => str_repeat( 'b', 64 ),
						),
					),
				)
			),
		);
	}

	/**
	 * Builds a successful Schedule Rollback Preview row.
	 *
	 * @param string $public_id Public action ID.
	 * @return array<string,mixed>
	 */
	private function successful_schedule_rollback_preview_history_row( $public_id = '44444444-4444-4444-8444-444444444444' ) {
		return array(
			'public_id'             => $public_id,
			'action_type'           => 'schedule_rollback_preview',
			'state'                 => 'succeeded',
			'requested_at'          => '2026-09-15 19:00:00',
			'client_result_summary' => 'Schedule rollback preview is ready. No schedule was changed.',
			'redacted_context_json' => wp_json_encode(
				array(
					'schedule_rollback_preview' => array(
						'schedule_id'                   => 'alynt_scan_upload',
						'current_cadence'               => 'every_30_minutes',
						'rollback_cadence'              => 'every_15_minutes',
						'current_next_run_at'           => '2026-09-15T19:30:00+00:00',
						'rollback_next_run_estimate_at' => '2026-09-15T19:15:00+00:00',
						'would_change'                  => true,
						'rollback_supported'            => false,
					),
				)
			),
		);
	}
}
