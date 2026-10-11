<?php
/**
 * Cleanup-preview snapshot fixtures for admin rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Provides reusable cleanup-preview snapshots.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Cleanup_Preview_Snapshot_Fixtures {
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
