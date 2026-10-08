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

	/**
	 * Builds a schedule rollback-preview capability summary for sanitizer coverage.
	 *
	 * @return array<string,mixed>
	 */
	private function schedule_rollback_preview_capability_summary() {
		return array(
			'protocol_version'    => 2,
			'enabled'             => true,
			'sodium_available'    => true,
			'allowed_actions'     => array( 'scan_upload_now', 'schedule_preview', 'schedule_rollback_preview' ),
			'schedule_management' => array(
				'protocol_version'           => 2,
				'capability_version'         => 1,
				'enabled'                    => true,
				'preview_only'               => true,
				'apply_supported'            => false,
				'rollback_preview_supported' => true,
				'rollback_supported'         => false,
				'schedules'                  => array(
					$this->alynt_scan_upload_schedule(
						array(
							'current_cadence'            => 'every_30_minutes',
							'supported_cadences'         => array( 'every_15_minutes', 'every_30_minutes' ),
							'rollback_preview_supported' => true,
							'rollback_supported'         => true,
						)
					),
				),
			),
			'last_action'         => array(
				'action_id'                 => '33333333-3333-4333-8333-333333333333',
				'action_type'               => 'schedule_rollback_preview',
				'state'                     => 'succeeded',
				'code'                      => 'schedule_rollback_preview_ready',
				'summary'                   => 'Schedule rollback preview is ready. No schedule was changed.',
				'schedule_rollback_preview' => array(
					'schedule_id'                           => 'alynt_scan_upload',
					'capability_version'                    => 1,
					'preview_action_id'                     => '33333333-3333-4333-8333-333333333333',
					'preview_fingerprint'                   => str_repeat( 'a', 64 ),
					'source_apply_action_id'                => '22222222-2222-4222-8222-222222222222',
					'rollback_metadata_fingerprint'         => str_repeat( 'd', 64 ),
					'current_cadence'                       => 'every_30_minutes',
					'applied_cadence'                       => 'every_30_minutes',
					'rollback_cadence'                      => 'every_15_minutes',
					'current_next_run_at'                   => '2026-09-15T18:53:55+00:00',
					'rollback_next_run_estimate_at'         => '2026-09-15T19:08:55+00:00',
					'current_schedule_fingerprint'          => str_repeat( 'c', 64 ),
					'expected_current_schedule_fingerprint' => str_repeat( 'c', 64 ),
					'previous_schedule_fingerprint'         => str_repeat( 'b', 64 ),
					'would_change'                          => true,
					'rollback_apply_supported'              => true,
					'rollback_supported'                    => true,
				),
			),
		);
	}

	/**
	 * Builds a schedule-apply capable summary for sanitizer coverage.
	 *
	 * @return array<string,mixed>
	 */
	private function schedule_apply_capability_summary() {
		return array(
			'protocol_version'    => 2,
			'enabled'             => true,
			'sodium_available'    => true,
			'allowed_actions'     => array( 'scan_upload_now', 'schedule_preview', 'schedule_apply' ),
			'schedule_management' => array(
				'protocol_version'   => 2,
				'capability_version' => 1,
				'enabled'            => true,
				'preview_only'       => false,
				'apply_supported'    => true,
				'rollback_supported' => false,
				'schedules'          => array(
					$this->alynt_scan_upload_schedule(
						array(
							'supported_cadences' => array( 'every_15_minutes', 'every_30_minutes', 'hourly' ),
						)
					),
				),
			),
			'last_action'         => array(
				'action_id'        => '11111111-1111-4111-8111-111111111111',
				'action_type'      => 'schedule_preview',
				'state'            => 'succeeded',
				'result_code'      => 'schedule_preview_ready',
				'result_summary'   => 'Schedule preview is ready.',
				'schedule_preview' => array(
					'schedule_id'                  => 'alynt_scan_upload',
					'current_cadence'              => 'every_15_minutes',
					'proposed_cadence'             => 'every_30_minutes',
					'would_change'                 => true,
					'apply_supported'              => true,
					'preview_action_id'            => '11111111-1111-4111-8111-111111111111',
					'preview_fingerprint'          => str_repeat( 'a', 64 ),
					'current_schedule_fingerprint' => str_repeat( 'b', 64 ),
					'capability_version'           => 1,
					'preview_expires_at'           => '2099-01-01T00:15:00+00:00',
				),
			),
		);
	}

	/**
	 * Builds a schedule-preview latest-action summary with code aliases.
	 *
	 * @return array<string,mixed>
	 */
	private function schedule_preview_alias_summary() {
		return array(
			'protocol_version' => 2,
			'enabled'          => true,
			'last_action'      => array(
				'action_id'        => '11111111-1111-4111-8111-111111111111',
				'action_type'      => 'schedule_preview',
				'state'            => 'succeeded',
				'code'             => 'schedule_preview_ready',
				'summary'          => 'Schedule preview is ready. No schedule was changed.',
				'schedule_preview' => array(
					'schedule_id'      => 'alynt_scan_upload',
					'current_cadence'  => 'every_15_minutes',
					'proposed_cadence' => 'every_30_minutes',
					'would_change'     => true,
				),
			),
		);
	}

	/**
	 * Builds a schedule-apply latest-action summary with next-run aliases.
	 *
	 * @return array<string,mixed>
	 */
	private function schedule_apply_alias_summary() {
		return array(
			'protocol_version' => 2,
			'enabled'          => true,
			'last_action'      => array(
				'action_id'      => '11111111-1111-4111-8111-111111111111',
				'action_type'    => 'schedule_apply',
				'state'          => 'succeeded',
				'code'           => 'schedule_apply_succeeded',
				'summary'        => 'Schedule apply completed for Alynt scan/upload.',
				'schedule_apply' => array(
					'schedule_id'          => 'alynt_scan_upload',
					'capability_version'   => 1,
					'preview_action_id'    => '22222222-2222-4222-8222-222222222222',
					'preview_fingerprint'  => str_repeat( 'a', 64 ),
					'previous_cadence'     => 'every_15_minutes',
					'applied_cadence'      => 'every_30_minutes',
					'previous_next_run_at' => '2026-09-15T18:30:03+00:00',
					'applied_next_run_at'  => '2026-09-15T18:53:55+00:00',
					'changed'              => true,
					'rollback_available'   => true,
					'rollback_metadata'    => array(
						'captured'                            => true,
						'available'                           => true,
						'reason'                              => 'schedule_rollback_runtime_not_implemented',
						'source_action_id'                    => '11111111-1111-4111-8111-111111111111',
						'source_preview_action_id'            => '22222222-2222-4222-8222-222222222222',
						'schedule_id'                         => 'alynt_scan_upload',
						'owner'                               => 'alynt_uploader',
						'previous_cadence'                    => 'every_15_minutes',
						'applied_cadence'                     => 'every_30_minutes',
						'previous_next_run_at'                => '2026-09-15T18:30:03+00:00',
						'applied_next_run_at'                 => '2026-09-15T18:53:55+00:00',
						'current_schedule_fingerprint_before' => str_repeat( 'b', 64 ),
						'current_schedule_fingerprint_after'  => str_repeat( 'c', 64 ),
						'captured_at'                         => '2026-09-15T18:24:12+00:00',
						'expires_at'                          => '2026-09-15T19:24:12+00:00',
					),
				),
			),
		);
	}
}
