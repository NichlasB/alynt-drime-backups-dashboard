<?php
/**
 * Scan/upload remote-action history fixtures for admin rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Provides reusable V2 scan/upload history rows.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Scan_Upload_History_Fixtures {
	/**
	 * Builds a short scan/upload history row fixture.
	 *
	 * @return array<string,mixed>
	 */
	private function scan_upload_history_row() {
		return array(
			'action_type'           => 'scan_upload_now',
			'state'                 => 'succeeded',
			'client_state'          => 'succeeded',
			'requested_at'          => '2026-09-15 18:00:00',
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
