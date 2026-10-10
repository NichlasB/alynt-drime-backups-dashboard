<?php
/**
 * Cleanup-preview evidence fixtures for admin rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Provides reusable cleanup-preview evidence payloads.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Cleanup_Preview_Evidence_Fixtures {
	/**
	 * Builds cleanup-preview evidence.
	 *
	 * @param bool $include_unsafe_category Whether to include an unsafe category for filtering assertions.
	 * @return array<string,mixed>
	 */
	private function cleanup_preview_evidence( $include_unsafe_category ) {
		$categories = array(
			array(
				'category'       => 'uploader_temp_artifacts',
				'eligible_count' => 2,
				'approx_bytes'   => 2048,
				'age_band'       => 'older_than_24h',
				'reason_code'    => 'safe_local_uploader_owned_temp_artifacts',
			),
		);

		if ( $include_unsafe_category ) {
			$categories[] = array(
				'category'       => 'server_backups',
				'eligible_count' => 99,
				'approx_bytes'   => 999999,
			);
		}

		return array(
			'preview_action_id'    => '44444444-4444-4444-8444-444444444444',
			'preview_fingerprint'  => str_repeat( 'e', 64 ),
			'capability_version'   => 1,
			'scope'                => 'safe_local_uploader_owned',
			'preview_created_at'   => '2026-09-29T12:00:00+00:00',
			'expires_at'           => '2026-09-29T12:15:00+00:00',
			'total_eligible_count' => 2,
			'total_approx_bytes'   => 2048,
			'apply_supported'      => true,
			'categories'           => $categories,
		);
	}
}
