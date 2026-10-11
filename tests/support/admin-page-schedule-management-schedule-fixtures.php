<?php
/**
 * Admin schedule-management schedule fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared schedule-management schedule row fixture helpers.
 */
trait Alynt_Drime_Backups_Dashboard_Schedule_Management_Schedule_Fixtures {
	/**
	 * Builds the Alynt scan/upload schedule row for fixture payloads.
	 *
	 * @param bool   $rollback_preview_supported Rollback preview supported.
	 * @param string $current_cadence Current cadence.
	 * @param int    $current_interval_seconds Current interval.
	 * @param string $current_next_run_at Current next run timestamp.
	 * @return array<string,mixed>
	 */
	private function alynt_scan_upload_schedule_row( $rollback_preview_supported, $current_cadence, $current_interval_seconds, $current_next_run_at ) {
		return array(
			'schedule_id'                    => 'alynt_scan_upload',
			'label'                          => 'Alynt scan/upload',
			'owner'                          => 'alynt_uploader',
			'manageable'                     => true,
			'current_cadence'                => $current_cadence,
			'current_interval_seconds'       => $current_interval_seconds,
			'current_next_run_at'            => $current_next_run_at,
			'supported_cadences'             => array( 'every_15_minutes', 'every_30_minutes', 'hourly' ),
			'minimum_interval_seconds'       => 900,
			'can_disable'                    => false,
			'requires_high_friction_disable' => true,
			'rollback_preview_supported'     => $rollback_preview_supported,
			'rollback_supported'             => false,
		);
	}
}
