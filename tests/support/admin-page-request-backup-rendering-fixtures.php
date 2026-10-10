<?php
/**
 * Shared request-backup rendering fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/admin-page-request-backup-history-fixtures.php';

/**
 * Provides request-backup rendering fixture builders.
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Request_Backup_Rendering_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Request_Backup_History_Fixtures;

	/**
	 * Builds a request-backup detail site row fixture.
	 *
	 * @param array<string,mixed> $overrides Site row overrides.
	 * @return array<string,mixed>
	 */
	private function request_backup_site( array $overrides = array() ) {
		return array_merge(
			array(
				'id'                            => 7,
				'enrollment_status'             => 'active',
				'polling_key_id'                => 'key-id',
				'has_polling_secret'            => '1',
				'action_key_id'                 => 'ak_test',
				'action_private_key_ciphertext' => 'ciphertext',
			),
			$overrides
		);
	}

	/**
	 * Builds a compact request-backup row site fixture.
	 *
	 * @param array<string,mixed> $overrides Site row overrides.
	 * @return array<string,mixed>
	 */
	private function request_backup_row_site( array $overrides = array() ) {
		return array_merge(
			array(
				'enrollment_status'  => 'active',
				'polling_key_id'     => 'key-id',
				'has_polling_secret' => '1',
			),
			$overrides
		);
	}

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
