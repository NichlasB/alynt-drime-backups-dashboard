<?php
/**
 * Admin schedule-apply history fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/admin-page-schedule-apply-metadata-fixtures.php';

/**
 * Shared schedule-apply history fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Schedule_Apply_History_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Schedule_Apply_Metadata_Fixtures;

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
						'rollback_metadata' => $this->schedule_apply_history_rollback_metadata(),
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
						'rollback_metadata' => $this->schedule_apply_detail_rollback_metadata(),
					),
				)
			),
		);
	}
}
