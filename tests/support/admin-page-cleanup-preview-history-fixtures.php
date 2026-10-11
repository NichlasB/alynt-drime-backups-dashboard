<?php
/**
 * Cleanup-preview history fixtures for admin rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Provides reusable cleanup-preview history rows.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Cleanup_Preview_History_Fixtures {
	/**
	 * Builds a cleanup-preview history row fixture.
	 *
	 * @return array<string,mixed>
	 */
	private function cleanup_preview_history_row() {
		return array(
			'action_type'           => 'cleanup_preview',
			'state'                 => 'succeeded',
			'client_state'          => 'succeeded',
			'requested_at'          => '2026-09-29 12:00:00',
			'client_result_summary' => 'Cleanup preview is ready. Nothing was deleted.',
			'redacted_context_json' => wp_json_encode(
				array(
					'cleanup_preview' => $this->cleanup_preview_evidence( false ),
				)
			),
		);
	}
}
