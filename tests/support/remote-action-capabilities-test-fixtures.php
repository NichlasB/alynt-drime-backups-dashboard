<?php
/**
 * Remote action capability test fixture builders.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared fixture builders for remote action capability tests.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Test_Fixtures {
	/**
	 * Creates a remote action capabilities helper.
	 *
	 * @return Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities
	 */
	private function remote_action_capabilities() {
		return new Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities();
	}

	/**
	 * Builds an Alynt scan/upload schedule capability summary.
	 *
	 * @param array<string,mixed> $overrides Overrides.
	 * @return array<string,mixed>
	 */
	private function alynt_scan_upload_schedule( array $overrides = array() ) {
		return array_merge(
			array(
				'schedule_id'              => 'alynt_scan_upload',
				'label'                    => 'Alynt scan/upload',
				'owner'                    => 'alynt_uploader',
				'manageable'               => true,
				'current_cadence'          => 'every_15_minutes',
				'supported_cadences'       => array( 'every_15_minutes' ),
				'minimum_interval_seconds' => 900,
				'can_disable'              => false,
				'rollback_preview_supported' => false,
				'rollback_supported'       => false,
			),
			$overrides
		);
	}

	/**
	 * Builds a representative valid remote-action capability summary.
	 *
	 * @return array<string,mixed>
	 */
	private function valid_remote_action_capability_summary() {
		return array(
			'protocol_version'             => 2,
			'enabled'                      => true,
			'key_id'                       => 'ak_valid.123',
			'allowed_actions'              => array(
				'scan_upload_now',
				'delete_backup',
				'scan_upload_now',
			),
			'sodium_available'             => true,
			'min_interval_seconds'         => 900,
			'one_running_action_per_site'  => true,
			'schedule_management'          => array(
				'protocol_version'   => 2,
				'capability_version' => 1,
				'enabled'            => true,
				'preview_only'       => true,
				'apply_supported'    => false,
				'rollback_supported' => false,
				'schedules'          => array(
					$this->alynt_scan_upload_schedule(
						array(
							'current_interval_seconds'       => 900,
							'current_next_run_at'            => '2026-06-25T16:45:00+00:00',
							'supported_cadences'             => array( 'every_15_minutes', 'every_15_minutes' ),
							'requires_high_friction_disable' => true,
							'rollback_supported'             => true,
							'extra_field'                    => 'ignored',
						)
					),
				),
			),
			'last_action'                  => array(
				'action_id'      => '11111111-1111-4111-8111-111111111111',
				'action_type'    => 'scan_upload_now',
				'state'          => 'succeeded',
				'requested_at'   => '2026-08-20T12:00:00+00:00',
				'completed_at'   => '2026-08-20T12:01:00+00:00',
				'result_code'    => 'ok',
				'result_summary' => str_repeat( 'A', 200 ),
				'counts'         => array(
					'found'            => 3,
					'queued'           => 1,
					'already_known'    => 2,
					'upload_attempted' => 1,
					'failed'           => -3,
				),
				'extra_field'    => 'ignored',
			),
			'extra_field'                  => 'ignored',
		);
	}

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
