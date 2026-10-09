<?php
/**
 * Snapshot fixtures for remote action dispatcher tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/remote-action-dispatcher-schedule-fixtures.php';

/**
 * Shared snapshot builders for remote action dispatcher tests.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_Snapshot_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_Schedule_Fixtures;

	/**
	 * Snapshot fixture.
	 *
	 * @param array<string,mixed> $remote_action_overrides Remote-action overrides.
	 * @return array<string,mixed>
	 */
	private function snapshot_row( array $remote_action_overrides = array() ) {
		$remote_actions = array_merge(
			array(
				'protocol_version'            => 2,
				'enabled'                     => true,
				'key_id'                      => 'ak_test',
				'allowed_actions'             => array( 'scan_upload_now', 'schedule_preview' ),
				'sodium_available'            => true,
				'min_interval_seconds'        => 3600,
				'one_running_action_per_site' => true,
				'preview_only'                => true,
				'apply_supported'             => false,
				'rollback_supported'          => false,
				'rollback_preview_supported'  => false,
			),
			$remote_action_overrides
		);

		return array(
			'payload_json' => wp_json_encode(
				array(
					'remote_actions' => array(
						'protocol_version'            => $remote_actions['protocol_version'],
						'enabled'                     => $remote_actions['enabled'],
						'key_id'                      => $remote_actions['key_id'],
						'allowed_actions'             => $remote_actions['allowed_actions'],
						'sodium_available'            => $remote_actions['sodium_available'],
						'min_interval_seconds'        => $remote_actions['min_interval_seconds'],
						'one_running_action_per_site' => $remote_actions['one_running_action_per_site'],
						'schedule_management'         => $this->dispatcher_schedule_management( $remote_actions ),
					),
				)
			),
		);
	}
}
