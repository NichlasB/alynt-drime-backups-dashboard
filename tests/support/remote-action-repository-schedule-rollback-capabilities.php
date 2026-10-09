<?php
/**
 * Remote action repository schedule rollback capability fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared schedule rollback capability lookup fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Schedule_Rollback_Capabilities {
	/**
	 * Returns sanitized capabilities that support rollback preview for tests.
	 *
	 * @return array<string,mixed>
	 */
	private function schedule_rollback_preview_lookup_capabilities() {
		return array(
			'enabled'             => true,
			'sodium_available'    => true,
			'allowed_actions'     => array( 'scan_upload_now', 'schedule_preview', 'schedule_apply', 'schedule_rollback_preview' ),
			'schedule_management' => array(
				'enabled'                    => true,
				'rollback_preview_supported' => true,
				'rollback_supported'         => false,
				'schedules'                  => array(
					array(
						'schedule_id'                => 'alynt_scan_upload',
						'manageable'                 => true,
						'rollback_preview_supported' => true,
						'rollback_supported'         => false,
					),
				),
			),
		);
	}
}
