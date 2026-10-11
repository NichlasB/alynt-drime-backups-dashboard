<?php
/**
 * Cleanup-preview fixtures for admin remote-action rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/admin-page-cleanup-preview-evidence-fixtures.php';
require_once __DIR__ . '/admin-page-cleanup-preview-last-action-fixtures.php';
require_once __DIR__ . '/admin-page-cleanup-preview-snapshot-fixtures.php';

/**
 * Provides reusable cleanup-preview snapshots and history rows.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Cleanup_Preview_Rendering_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Test_Cleanup_Preview_Evidence_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Test_Cleanup_Preview_Last_Action_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Test_Cleanup_Preview_Snapshot_Fixtures;

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
