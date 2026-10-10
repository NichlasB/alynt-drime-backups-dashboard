<?php
/**
 * Shared fixtures for admin remote-action rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/admin-page-remote-action-context-fixtures.php';
require_once __DIR__ . '/admin-page-schedule-management-history-fixtures.php';
require_once __DIR__ . '/admin-page-schedule-management-rendering-fixtures.php';

/**
 * Provides reusable V2 remote-action rows and snapshots.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Remote_Action_Rendering_Fixtures {
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
	 * Builds a cleanup-preview snapshot fixture.
	 *
	 * @param array<string,mixed> $remote_action_overrides Remote-action overrides.
	 * @param array<string,mixed> $cleanup_overrides Cleanup management overrides.
	 * @param array<string,mixed> $additional_remote_action_data Additional remote-action data.
	 * @return array<string,array<string,mixed>>
	 */
	private function cleanup_preview_snapshot(
		array $remote_action_overrides = array(),
		array $cleanup_overrides = array(),
		array $additional_remote_action_data = array()
	) {
		return array(
			'decoded_payload' => array(
				'remote_actions' => array_merge(
					array(
						'protocol_version'   => 2,
						'enabled'            => true,
						'key_id'             => 'ak_test',
						'allowed_actions'    => array( 'scan_upload_now', 'cleanup_preview' ),
						'sodium_available'   => true,
						'cleanup_management' => array_merge(
							array(
								'protocol_version'        => 2,
								'capability_version'      => 1,
								'enabled'                 => true,
								'preview_supported'       => true,
								'apply_supported'         => false,
								'scope'                   => 'safe_local_uploader_owned',
								'supported_categories'    => array( 'uploader_temp_artifacts' ),
								'max_preview_age_seconds' => 900,
							),
							$cleanup_overrides
						),
					),
					$remote_action_overrides,
					$additional_remote_action_data
				),
			),
		);
	}
}
