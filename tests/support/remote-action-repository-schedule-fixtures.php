<?php
/**
 * Remote action repository schedule test fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared remote action repository schedule fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Schedule_Fixtures {
	/**
	 * Returns an expired schedule-preview row.
	 *
	 * @param string $preview_id Preview action public ID.
	 * @return array<string,mixed>
	 */
	private function expired_schedule_preview_row( $preview_id ) {
		return array(
			'id'                    => 321,
			'public_id'             => $preview_id,
			'dashboard_site_id'     => 44,
			'action_type'           => 'schedule_preview',
			'state'                 => 'succeeded',
			'completed_at'          => '2026-08-20 12:00:00',
			'redacted_context_json' => wp_json_encode(
				array(
					'schedule_preview' => array(
						'preview_action_id'    => $preview_id,
						'preview_fingerprint'  => str_repeat( 'a', 64 ),
						'schedule_id'          => 'alynt_scan_upload',
						'current_cadence'      => 'every_15_minutes',
						'proposed_cadence'     => 'every_30_minutes',
						'capability_version'   => 1,
						'preview_expires_at'   => '2026-08-20T12:05:00+00:00',
						'would_change'         => true,
						'apply_supported'      => true,
						'rollback_supported'   => false,
					),
				)
			),
		);
	}

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
