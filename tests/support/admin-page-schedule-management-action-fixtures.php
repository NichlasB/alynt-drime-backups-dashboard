<?php
/**
 * Admin schedule-management action fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared schedule-management action fixture helpers.
 */
trait Alynt_Drime_Backups_Dashboard_Schedule_Management_Action_Fixtures {
	/**
	 * Builds allowed actions for schedule-management fixture payloads.
	 *
	 * @param bool $apply_supported Apply supported.
	 * @param bool $rollback_preview_supported Rollback preview supported.
	 * @return array<int,string>
	 */
	private function schedule_management_allowed_actions( $apply_supported, $rollback_preview_supported ) {
		$allowed_actions = array( 'scan_upload_now', 'schedule_preview' );

		if ( $apply_supported || $rollback_preview_supported ) {
			$allowed_actions[] = 'schedule_apply';
		}

		if ( $rollback_preview_supported ) {
			$allowed_actions[] = 'schedule_rollback_preview';
		}

		return $allowed_actions;
	}
}
