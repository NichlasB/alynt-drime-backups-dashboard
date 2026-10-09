<?php
/**
 * Shared V2 context fixtures for admin remote-action rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Provides reusable V2-capable site and snapshot fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Remote_Action_Context_Fixtures {
	/**
	 * Gets a reusable V2-capable site row for remote-action history tests.
	 *
	 * @return array<string,mixed>
	 */
	private function remote_action_history_site() {
		return array(
			'id'                            => 7,
			'enrollment_status'             => 'active',
			'polling_key_id'                => 'key-id',
			'has_polling_secret'            => '1',
			'action_key_id'                 => 'ak_test',
			'action_private_key_ciphertext' => 'ciphertext',
		);
	}

	/**
	 * Gets a reusable V2-capable snapshot for remote-action history tests.
	 *
	 * @return array<string,mixed>
	 */
	private function remote_action_history_snapshot() {
		return array(
			'decoded_payload' => array(
				'remote_actions' => array(
					'protocol_version' => 2,
					'enabled'          => true,
					'allowed_actions'  => array( 'scan_upload_now', 'schedule_preview', 'schedule_apply' ),
					'sodium_available' => true,
				),
			),
		);
	}
}
