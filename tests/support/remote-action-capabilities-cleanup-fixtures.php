<?php
/**
 * Remote action cleanup capability test fixture builders.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared cleanup fixture builders for remote action capability tests.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Cleanup_Fixtures {
	/**
	 * Builds a cleanup-preview capability summary with unsafe fields for sanitizer coverage.
	 *
	 * @return array<string,mixed>
	 */
	private function cleanup_preview_capability_summary() {
		return array(
			'protocol_version'   => 2,
			'enabled'            => true,
			'sodium_available'   => true,
			'allowed_actions'    => array( 'scan_upload_now', 'cleanup_preview', 'cleanup_apply' ),
			'cleanup_management' => array(
				'protocol_version'        => 2,
				'capability_version'      => 1,
				'enabled'                 => true,
				'preview_supported'       => true,
				'apply_supported'         => false,
				'scope'                   => 'safe_local_uploader_owned',
				'supported_categories'    => array( 'uploader_temp_artifacts', 'server_backups' ),
				'requires_fresh_preview'  => true,
				'max_preview_age_seconds' => 900,
				'paths_exposed'           => true,
			),
			'last_action'        => array(
				'action_id'       => '44444444-4444-4444-8444-444444444444',
				'action_type'     => 'cleanup_preview',
				'state'           => 'succeeded',
				'code'            => 'cleanup_preview_ready',
				'summary'         => 'Cleanup preview is ready. Nothing was deleted.',
				'cleanup_preview' => array(
					'preview_action_id'    => '44444444-4444-4444-8444-444444444444',
					'preview_fingerprint'  => str_repeat( 'e', 64 ),
					'capability_version'   => 1,
					'scope'                => 'safe_local_uploader_owned',
					'preview_created_at'   => '2026-09-29T12:00:00+00:00',
					'expires_at'           => '2026-09-29T12:15:00+00:00',
					'total_eligible_count' => 2,
					'total_approx_bytes'   => 2048,
					'apply_supported'      => true,
					'categories'           => array(
						array(
							'category'       => 'uploader_temp_artifacts',
							'eligible_count' => 2,
							'approx_bytes'   => 2048,
							'age_band'       => 'older_than_24h',
							'reason_code'    => 'safe_local_uploader_owned_temp_artifacts',
						),
						array(
							'category'       => 'server_backups',
							'eligible_count' => 99,
						),
					),
				),
			),
		);
	}
}
