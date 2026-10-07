<?php
/**
 * Admin schedule-management payload fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared schedule-management payload fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Schedule_Management_Test_Fixtures {
	/**
	 * Creates a sanitized snapshot payload with preview-only schedule capability.
	 *
	 * @param bool $apply_supported Apply supported.
	 * @param bool $rollback_preview_supported Rollback preview supported.
	 * @return array<string,mixed>
	 */
	private function payload( $apply_supported = false, $rollback_preview_supported = false ) {
		return array(
			'remote_actions' => array(
				'protocol_version'    => 2,
				'enabled'             => true,
				'sodium_available'    => true,
				'allowed_actions'     => $rollback_preview_supported ? array( 'scan_upload_now', 'schedule_preview', 'schedule_apply', 'schedule_rollback_preview' ) : ( $apply_supported ? array( 'scan_upload_now', 'schedule_preview', 'schedule_apply' ) : array( 'scan_upload_now', 'schedule_preview' ) ),
				'schedule_management' => array(
					'protocol_version'           => 2,
					'capability_version'         => 1,
					'enabled'                    => true,
					'preview_only'               => ! $apply_supported,
					'apply_supported'            => $apply_supported,
					'rollback_preview_supported' => $rollback_preview_supported,
					'rollback_supported'         => false,
					'schedules'                  => array(
						array(
							'schedule_id'                    => 'alynt_scan_upload',
							'label'                          => 'Alynt scan/upload',
							'owner'                          => 'alynt_uploader',
							'manageable'                     => true,
							'current_cadence'                => 'every_15_minutes',
							'current_interval_seconds'       => 900,
							'current_next_run_at'            => '2026-06-25T16:45:00+00:00',
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
		);
	}
}
