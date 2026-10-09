<?php
/**
 * Remote action cleanup latest-action capability fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared cleanup latest-action fixture builders for remote action capability tests.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Cleanup_Last_Action_Fixtures {
	/**
	 * Builds a cleanup-preview latest-action summary with unsafe fields for sanitizer coverage.
	 *
	 * @return array<string,mixed>
	 */
	private function cleanup_preview_last_action_summary() {
		return array(
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
		);
	}
}
