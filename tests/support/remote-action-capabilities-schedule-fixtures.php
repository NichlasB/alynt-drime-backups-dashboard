<?php
/**
 * Remote action schedule capability test fixture builders.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared schedule fixture builders for remote action capability tests.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Schedule_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Schedule_Alias_Fixtures;

	/**
	 * Builds an Alynt scan/upload schedule capability summary.
	 *
	 * @param array<string,mixed> $overrides Overrides.
	 * @return array<string,mixed>
	 */
	private function alynt_scan_upload_schedule( array $overrides = array() ) {
		return array_merge(
			array(
				'schedule_id'                => 'alynt_scan_upload',
				'label'                      => 'Alynt scan/upload',
				'owner'                      => 'alynt_uploader',
				'manageable'                 => true,
				'current_cadence'            => 'every_15_minutes',
				'supported_cadences'         => array( 'every_15_minutes' ),
				'minimum_interval_seconds'   => 900,
				'can_disable'                => false,
				'rollback_preview_supported' => false,
				'rollback_supported'         => false,
			),
			$overrides
		);
	}

	/**
	 * Builds a schedule-apply capable summary for sanitizer coverage.
	 *
	 * @return array<string,mixed>
	 */
	private function schedule_apply_capability_summary() {
		return array(
			'protocol_version'    => 2,
			'enabled'             => true,
			'sodium_available'    => true,
			'allowed_actions'     => array( 'scan_upload_now', 'schedule_preview', 'schedule_apply' ),
			'schedule_management' => array(
				'protocol_version'   => 2,
				'capability_version' => 1,
				'enabled'            => true,
				'preview_only'       => false,
				'apply_supported'    => true,
				'rollback_supported' => false,
				'schedules'          => array(
					$this->alynt_scan_upload_schedule(
						array(
							'supported_cadences' => array( 'every_15_minutes', 'every_30_minutes', 'hourly' ),
						)
					),
				),
			),
			'last_action'         => array(
				'action_id'        => '11111111-1111-4111-8111-111111111111',
				'action_type'      => 'schedule_preview',
				'state'            => 'succeeded',
				'result_code'      => 'schedule_preview_ready',
				'result_summary'   => 'Schedule preview is ready.',
				'schedule_preview' => array(
					'schedule_id'                  => 'alynt_scan_upload',
					'current_cadence'              => 'every_15_minutes',
					'proposed_cadence'             => 'every_30_minutes',
					'would_change'                 => true,
					'apply_supported'              => true,
					'preview_action_id'            => '11111111-1111-4111-8111-111111111111',
					'preview_fingerprint'          => str_repeat( 'a', 64 ),
					'current_schedule_fingerprint' => str_repeat( 'b', 64 ),
					'capability_version'           => 1,
					'preview_expires_at'           => '2099-01-01T00:15:00+00:00',
				),
			),
		);
	}

}
