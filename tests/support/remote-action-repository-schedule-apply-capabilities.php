<?php
/**
 * Remote action repository schedule apply capability fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared schedule apply capability lookup fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Schedule_Apply_Capabilities {
	/**
	 * Returns sanitized capabilities that support schedule apply for tests.
	 *
	 * @return array<string,mixed>
	 */
	private function schedule_apply_lookup_capabilities() {
		return array(
			'allowed_actions'      => array( 'scan_upload_now', 'schedule_preview', 'schedule_apply' ),
			'schedule_management' => array(
				'schedules'         => array(
					array(
						'id'                 => 'alynt_scan_upload',
						'apply_supported'    => true,
						'supported_cadences' => array( 'every_30_minutes' ),
					),
				),
				'apply_supported'   => true,
				'preview_supported' => true,
			),
		);
	}
}
