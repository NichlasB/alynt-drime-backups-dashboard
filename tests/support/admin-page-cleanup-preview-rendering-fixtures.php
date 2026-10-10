<?php
/**
 * Cleanup-preview fixtures for admin remote-action rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/admin-page-cleanup-preview-evidence-fixtures.php';

/**
 * Provides reusable cleanup-preview snapshots and history rows.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Cleanup_Preview_Rendering_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Test_Cleanup_Preview_Evidence_Fixtures;

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

	/**
	 * Builds a latest cleanup-preview action summary fixture.
	 *
	 * @param bool $include_unsafe_category Whether to include an unsafe category for filtering assertions.
	 * @return array<string,mixed>
	 */
	private function cleanup_preview_last_action_summary( $include_unsafe_category = true ) {
		return array(
			'action_id'       => '44444444-4444-4444-8444-444444444444',
			'action_type'     => 'cleanup_preview',
			'state'           => 'succeeded',
			'code'            => 'cleanup_preview_ready',
			'summary'         => 'Cleanup preview is ready. Nothing was deleted.',
			'cleanup_preview' => $this->cleanup_preview_evidence( $include_unsafe_category ),
		);
	}

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
