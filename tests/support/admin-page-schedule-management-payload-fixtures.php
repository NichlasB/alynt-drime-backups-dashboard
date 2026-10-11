<?php
/**
 * Admin schedule-management payload fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/admin-page-schedule-management-action-fixtures.php';
require_once __DIR__ . '/admin-page-schedule-management-history-fixtures.php';
require_once __DIR__ . '/admin-page-schedule-management-schedule-fixtures.php';

/**
 * Shared schedule-management payload fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Schedule_Management_Test_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Schedule_Management_Action_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Schedule_Management_History_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Schedule_Management_Schedule_Fixtures;

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
				'allowed_actions'     => $this->schedule_management_allowed_actions( $apply_supported, $rollback_preview_supported ),
				'schedule_management' => array(
					'protocol_version'           => 2,
					'capability_version'         => 1,
					'enabled'                    => true,
					'preview_only'               => ! $apply_supported,
					'apply_supported'            => $apply_supported,
					'rollback_preview_supported' => $rollback_preview_supported,
					'rollback_supported'         => false,
					'schedules'                  => array(
						$this->alynt_scan_upload_schedule_row(
							$rollback_preview_supported,
							'every_15_minutes',
							900,
							'2026-06-25T16:45:00+00:00'
						),
					),
				),
			),
		);
	}
}
