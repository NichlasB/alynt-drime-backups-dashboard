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
	 * Builds a successful Schedule Apply row for history detail rendering.
	 *
	 * @return array<string,mixed>
	 */
	private function successful_schedule_apply_detail_history_row() {
		return array(
			'action_type'           => 'schedule_apply',
			'state'                 => 'succeeded',
			'requested_at'          => '2026-09-15 18:23:43',
			'client_result_summary' => 'Schedule apply completed for Alynt scan/upload.',
			'redacted_context_json' => wp_json_encode(
				array(
					'schedule_apply' => array(
						'previous_cadence'  => 'every_15_minutes',
						'applied_cadence'   => 'every_30_minutes',
						'new_next_run_at'   => '2026-09-15T18:53:55+00:00',
						'rollback_metadata' => array(
							'captured'  => true,
							'available' => false,
							'reason'    => 'schedule_rollback_runtime_not_implemented',
							'expires_at' => '2026-09-15T19:24:12+00:00',
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

	/**
	 * Builds pending-cadence schedule history rows.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private function pending_schedule_cadence_history_rows() {
		return array(
			array(
				'action_type'           => 'schedule_preview',
				'state'                 => 'succeeded',
				'requested_at'          => '2026-09-15 18:10:00',
				'client_result_summary' => 'Schedule preview completed for Alynt scan/upload.',
				'redacted_context_json' => wp_json_encode(
					array(
						'schedule_preview' => array(
							'proposed_cadence' => 'every_15_minutes',
						),
					)
				),
			),
			array(
				'action_type'           => 'schedule_apply',
				'state'                 => 'succeeded',
				'requested_at'          => '2026-09-15 18:20:00',
				'client_result_summary' => 'Schedule apply completed for Alynt scan/upload.',
				'redacted_context_json' => wp_json_encode(
					array(
						'schedule_apply' => array(
							'applied_cadence' => 'every_15_minutes',
						),
					)
				),
			),
		);
	}
}
