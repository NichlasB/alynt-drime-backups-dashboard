<?php
/**
 * Schedule-management fixtures for admin remote-action rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/admin-page-schedule-management-action-fixtures.php';
require_once __DIR__ . '/admin-page-schedule-management-schedule-fixtures.php';

/**
 * Provides reusable schedule-management snapshots.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Schedule_Management_Rendering_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Schedule_Management_Action_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Schedule_Management_Schedule_Fixtures;

	/**
	 * Gets a reusable schedule-management snapshot.
	 *
	 * @param bool $rollback_preview_supported Whether rollback preview is advertised.
	 * @return array<string,mixed>
	 */
	private function schedule_management_snapshot( $rollback_preview_supported ) {
		return array(
			'decoded_payload' => array(
				'remote_actions' => array(
					'protocol_version'    => 2,
					'enabled'             => true,
					'allowed_actions'     => $this->schedule_management_allowed_actions( true, $rollback_preview_supported ),
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
							$this->alynt_scan_upload_schedule_row(
								$rollback_preview_supported,
								'every_30_minutes',
								1800,
								'2026-09-15T18:53:55+00:00'
							),
						),
					),
				),
			),
		);
	}
}
