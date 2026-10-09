<?php
/**
 * Diagnostics cleanup-preview payload fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared cleanup-preview payload fixture builders.
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Cleanup_Preview_Payload_Fixtures {
	/**
	 * Creates one cleanup-preview payload fixture.
	 *
	 * @param bool $preview_supported Whether cleanup preview is supported.
	 * @param bool $apply_supported Whether cleanup apply is reported as supported.
	 * @param bool $cleanup_apply_available Whether cleanup apply is advertised.
	 * @param bool $include_max_age Whether to include max preview age.
	 * @return array<string,mixed>
	 */
	private function cleanup_preview_payload( $preview_supported, $apply_supported, $cleanup_apply_available, $include_max_age ) {
		$cleanup_management = array(
			'protocol_version'     => 2,
			'capability_version'   => 1,
			'enabled'              => true,
			'preview_supported'    => $preview_supported,
			'apply_supported'      => $apply_supported,
			'scope'                => 'safe_local_uploader_owned',
			'supported_categories' => array( 'uploader_temp_artifacts' ),
		);

		if ( $cleanup_apply_available ) {
			$cleanup_management['cleanup_apply_available'] = true;
		}

		if ( $include_max_age ) {
			$cleanup_management['max_preview_age_seconds'] = 900;
		}

		return array(
			'remote_actions' => array(
				'protocol_version'   => 2,
				'enabled'            => true,
				'cleanup_management' => $cleanup_management,
			),
		);
	}
}
