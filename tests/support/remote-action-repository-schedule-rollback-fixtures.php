<?php
/**
 * Remote action repository schedule rollback test fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared remote action repository schedule rollback fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Schedule_Rollback_Fixtures {
	/**
	 * Returns a successful schedule-apply row with rollback metadata.
	 *
	 * @param string $apply_id Apply action public ID.
	 * @return array<string,mixed>
	 */
	private function successful_schedule_apply_row( $apply_id ) {
		return array(
			'id'                    => 321,
			'public_id'             => $apply_id,
			'dashboard_site_id'     => 44,
			'action_type'           => 'schedule_apply',
			'state'                 => 'succeeded',
			'completed_at'          => '2026-09-15 18:24:12',
			'redacted_context_json' => wp_json_encode(
				array(
					'schedule_apply' => array(
						'schedule_id'        => 'alynt_scan_upload',
						'previous_cadence'   => 'every_15_minutes',
						'applied_cadence'    => 'every_30_minutes',
						'capability_version' => 1,
						'rollback_metadata'  => array(
							'captured'                      => true,
							'source_action_id'              => $apply_id,
							'schedule_id'                   => 'alynt_scan_upload',
							'previous_cadence'              => 'every_15_minutes',
							'applied_cadence'               => 'every_30_minutes',
							'rollback_metadata_fingerprint' => str_repeat( 'b', 64 ),
							'expires_at'                    => '2026-09-15T19:24:12+00:00',
						),
					),
				)
			),
		);
	}

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
