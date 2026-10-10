<?php
/**
 * Shared fixtures for admin remote-action rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/admin-page-cleanup-preview-rendering-fixtures.php';
require_once __DIR__ . '/admin-page-remote-action-context-fixtures.php';
require_once __DIR__ . '/admin-page-schedule-management-history-fixtures.php';
require_once __DIR__ . '/admin-page-schedule-management-rendering-fixtures.php';

/**
 * Provides reusable V2 remote-action rows and snapshots.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Remote_Action_Rendering_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Test_Cleanup_Preview_Rendering_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Test_Remote_Action_Context_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Schedule_Management_History_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Test_Schedule_Management_Rendering_Fixtures;

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

	/**
	 * Builds a remote-action history row fixture.
	 *
	 * @param array<string,mixed> $overrides Row overrides.
	 * @param array<string,mixed> $context Redacted context payload.
	 * @return array<string,mixed>
	 */
	private function history_row( array $overrides, array $context ) {
		return array_merge(
			array(
				'action_type'           => 'scan_upload_now',
				'state'                 => 'succeeded',
				'client_state'          => 'succeeded',
				'requested_at'          => '2026-09-15 18:00:00',
				'client_result_summary' => '',
				'redacted_context_json' => wp_json_encode( $context ),
			),
			$overrides
		);
	}

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
