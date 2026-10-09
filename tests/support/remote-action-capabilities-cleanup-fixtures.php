<?php
/**
 * Remote action cleanup capability test fixture builders.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/remote-action-capabilities-cleanup-last-action-fixtures.php';

/**
 * Shared cleanup fixture builders for remote action capability tests.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Cleanup_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Cleanup_Last_Action_Fixtures;

	/**
	 * Builds a cleanup-preview capability summary with unsafe fields for sanitizer coverage.
	 *
	 * @return array<string,mixed>
	 */
	private function cleanup_preview_capability_summary() {
		return array(
			'protocol_version'   => 2,
			'enabled'            => true,
			'sodium_available'   => true,
			'allowed_actions'    => array( 'scan_upload_now', 'cleanup_preview', 'cleanup_apply' ),
			'cleanup_management' => array(
				'protocol_version'        => 2,
				'capability_version'      => 1,
				'enabled'                 => true,
				'preview_supported'       => true,
				'apply_supported'         => false,
				'scope'                   => 'safe_local_uploader_owned',
				'supported_categories'    => array( 'uploader_temp_artifacts', 'server_backups' ),
				'requires_fresh_preview'  => true,
				'max_preview_age_seconds' => 900,
				'paths_exposed'           => true,
			),
			'last_action'        => $this->cleanup_preview_last_action_summary(),
		);
	}
}
