<?php
/**
 * Shared fixtures for admin remote-action rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Provides reusable V2 remote-action rows and snapshots.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Remote_Action_Rendering_Fixtures {
	/**
	 * Gets a reusable V2-capable site row for remote-action history tests.
	 *
	 * @return array<string,mixed>
	 */
	private function remote_action_history_site() {
		return array(
			'id'                            => 7,
			'enrollment_status'             => 'active',
			'polling_key_id'                => 'key-id',
			'has_polling_secret'            => '1',
			'action_key_id'                 => 'ak_test',
			'action_private_key_ciphertext' => 'ciphertext',
		);
	}

	/**
	 * Gets a reusable V2-capable snapshot for remote-action history tests.
	 *
	 * @return array<string,mixed>
	 */
	private function remote_action_history_snapshot() {
		return array(
			'decoded_payload' => array(
				'remote_actions' => array(
					'protocol_version' => 2,
					'enabled'          => true,
					'allowed_actions'  => array( 'scan_upload_now', 'schedule_preview', 'schedule_apply' ),
					'sodium_available' => true,
				),
			),
		);
	}

	/**
	 * Gets a reusable schedule-management snapshot.
	 *
	 * @param bool $rollback_preview_supported Whether rollback preview is advertised.
	 * @return array<string,mixed>
	 */
	private function schedule_management_snapshot( $rollback_preview_supported ) {
		$allowed_actions = array( 'scan_upload_now', 'schedule_preview', 'schedule_apply' );

		if ( $rollback_preview_supported ) {
			$allowed_actions[] = 'schedule_rollback_preview';
		}

		return array(
			'decoded_payload' => array(
				'remote_actions' => array(
					'protocol_version' => 2,
					'enabled'          => true,
					'allowed_actions'  => $allowed_actions,
					'sodium_available' => true,
					'schedule_management' => array(
						'protocol_version'            => 2,
						'capability_version'          => 1,
						'enabled'                     => true,
						'preview_only'                => false,
						'apply_supported'             => true,
						'rollback_preview_supported'  => $rollback_preview_supported,
						'rollback_supported'          => false,
						'schedules'                   => array(
							array(
								'schedule_id'                    => 'alynt_scan_upload',
								'label'                          => 'Alynt scan/upload',
								'owner'                          => 'alynt_uploader',
								'manageable'                     => true,
								'current_cadence'                => 'every_30_minutes',
								'current_interval_seconds'       => 1800,
								'current_next_run_at'            => '2026-09-15T18:53:55+00:00',
								'supported_cadences'             => array( 'every_15_minutes', 'every_30_minutes', 'hourly' ),
								'minimum_interval_seconds'       => 900,
								'can_disable'                    => false,
								'requires_high_friction_disable' => true,
								'rollback_preview_supported'     => $rollback_preview_supported,
								'rollback_supported'             => false,
							),
						),
					),
				),
			),
		);
	}

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
}