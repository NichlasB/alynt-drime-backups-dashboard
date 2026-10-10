<?php
/**
 * Admin schedule-management history fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/admin-page-pending-schedule-history-fixtures.php';
require_once __DIR__ . '/admin-page-schedule-rollback-history-fixtures.php';

/**
 * Shared schedule-management history fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Schedule_Management_History_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Pending_Schedule_History_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Schedule_Rollback_History_Fixtures;

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

}
