<?php
/**
 * Admin pending-schedule history fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared pending-schedule history fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Pending_Schedule_History_Fixtures {
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
