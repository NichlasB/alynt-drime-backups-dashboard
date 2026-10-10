<?php
/**
 * Shared request-backup payload fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Provides request-backup remote-action payload fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Request_Backup_Payload_Fixtures {
	/**
	 * Builds a request-backup remote-action payload fixture.
	 *
	 * @param array<string,mixed> $remote_action_overrides Remote-action overrides.
	 * @return array<string,array<string,mixed>>
	 */
	private function request_backup_payload( array $remote_action_overrides = array() ) {
		return array(
			'remote_actions' => array_merge(
				array(
					'protocol_version' => 2,
					'enabled'          => true,
					'allowed_actions'  => array( 'scan_upload_now' ),
					'sodium_available' => true,
				),
				$remote_action_overrides
			),
		);
	}

	/**
	 * Builds a request-backup remote-action snapshot fixture.
	 *
	 * @param array<string,mixed> $remote_action_overrides Remote-action overrides.
	 * @return array<string,array<string,mixed>>
	 */
	private function request_backup_snapshot( array $remote_action_overrides = array() ) {
		return array(
			'decoded_payload' => $this->request_backup_payload( $remote_action_overrides ),
		);
	}
}
