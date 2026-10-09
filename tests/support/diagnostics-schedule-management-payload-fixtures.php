<?php
/**
 * Diagnostics schedule-management payload fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared schedule-management payload fixture builders.
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Schedule_Management_Payload_Fixtures {
	/**
	 * Creates one schedule-management payload fixture.
	 *
	 * @param bool   $preview_only Whether the client reports preview-only support.
	 * @param bool   $apply_supported Whether schedule apply is supported.
	 * @param bool   $rollback_preview_supported Whether rollback preview is supported.
	 * @param string $current_cadence Current schedule cadence.
	 * @return array<string,mixed>
	 */
	private function schedule_management_payload( $preview_only, $apply_supported, $rollback_preview_supported, $current_cadence ) {
		return array(
			'remote_actions' => array(
				'protocol_version'    => 2,
				'enabled'             => true,
				'schedule_management' => array(
					'protocol_version'           => 2,
					'capability_version'         => 1,
					'enabled'                    => true,
					'preview_only'               => $preview_only,
					'apply_supported'            => $apply_supported,
					'rollback_preview_supported' => $rollback_preview_supported,
					'rollback_supported'         => false,
					'schedules'                  => array(
						array(
							'schedule_id'                => 'alynt_scan_upload',
							'current_cadence'            => $current_cadence,
							'rollback_preview_supported' => $rollback_preview_supported,
						),
					),
				),
			),
		);
	}
}
