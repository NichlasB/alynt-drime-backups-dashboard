<?php
/**
 * Remote-action history fixtures for admin rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/admin-page-remote-action-history-row-fixtures.php';
require_once __DIR__ . '/admin-page-scan-upload-history-fixtures.php';

/**
 * Provides reusable V2 remote-action history rows.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Remote_Action_History_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Test_Remote_Action_History_Row_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Test_Scan_Upload_History_Fixtures;

	/**
	 * Gets reusable remote-action rows for history filter tests.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private function remote_action_history_rows() {
		return array(
			array(
				'action_type'           => 'scan_upload_now',
				'state'                 => 'rate_limited',
				'client_state'          => 'rate_limited',
				'requested_at'          => '2026-09-15 18:00:00',
				'client_result_summary' => 'Rate limited by client.',
			),
			array(
				'action_type'           => 'schedule_preview',
				'state'                 => 'succeeded',
				'client_state'          => 'succeeded',
				'requested_at'          => '2026-09-15 18:10:00',
				'client_result_summary' => 'Schedule preview completed for Alynt scan/upload.',
			),
			array(
				'action_type'           => 'schedule_apply',
				'state'                 => 'succeeded',
				'client_state'          => 'succeeded',
				'requested_at'          => '2026-09-15 18:20:00',
				'client_result_summary' => 'Schedule apply completed for Alynt scan/upload.',
			),
		);
	}
}
