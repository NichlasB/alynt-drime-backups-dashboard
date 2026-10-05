<?php
/**
 * Remote action capability test fixture builders.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared fixture builders for remote action capability tests.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Test_Fixtures {
	/**
	 * Creates a remote action capabilities helper.
	 *
	 * @return Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities
	 */
	private function remote_action_capabilities() {
		return new Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities();
	}

	/**
	 * Builds an Alynt scan/upload schedule capability summary.
	 *
	 * @param array<string,mixed> $overrides Overrides.
	 * @return array<string,mixed>
	 */
	private function alynt_scan_upload_schedule( array $overrides = array() ) {
		return array_merge(
			array(
				'schedule_id'              => 'alynt_scan_upload',
				'label'                    => 'Alynt scan/upload',
				'owner'                    => 'alynt_uploader',
				'manageable'               => true,
				'current_cadence'          => 'every_15_minutes',
				'supported_cadences'       => array( 'every_15_minutes' ),
				'minimum_interval_seconds' => 900,
				'can_disable'              => false,
				'rollback_preview_supported' => false,
				'rollback_supported'       => false,
			),
			$overrides
		);
	}
}
