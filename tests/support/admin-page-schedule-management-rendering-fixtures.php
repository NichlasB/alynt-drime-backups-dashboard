<?php
/**
 * Schedule-management fixtures for admin remote-action rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Provides reusable schedule-management snapshots.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Schedule_Management_Rendering_Fixtures {
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
					'protocol_version'    => 2,
					'enabled'             => true,
					'allowed_actions'     => $allowed_actions,
					'sodium_available'    => true,
					'schedule_management' => array(
						'protocol_version'           => 2,
						'capability_version'         => 1,
						'enabled'                    => true,
						'preview_only'               => false,
						'apply_supported'            => true,
						'rollback_preview_supported' => $rollback_preview_supported,
						'rollback_supported'         => false,
						'schedules'                  => array(
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
}
