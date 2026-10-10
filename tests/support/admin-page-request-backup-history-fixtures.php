<?php
/**
 * Shared request-backup history fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Provides request-backup history row fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Request_Backup_History_Fixtures {
	/**
	 * Builds a successful request-backup history row fixture.
	 *
	 * @return array<string,mixed>
	 */
	private function request_backup_history_row() {
		return array(
			'action_type'           => 'scan_upload_now',
			'state'                 => 'succeeded',
			'client_state'          => 'succeeded',
			'requested_at'          => '2026-08-20 12:00:00',
			'result_summary'        => 'Stored locally only.',
			'client_result_summary' => 'Scan completed safely.',
			'client_counts_json'    => wp_json_encode(
				array(
					'found'            => 2,
					'queued'           => 0,
					'already_known'    => 1,
					'upload_attempted' => 1,
					'failed'           => 0,
				)
			),
		);
	}
}
