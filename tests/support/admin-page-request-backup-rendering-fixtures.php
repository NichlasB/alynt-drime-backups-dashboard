<?php
/**
 * Shared request-backup rendering fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/admin-page-request-backup-history-fixtures.php';
require_once __DIR__ . '/admin-page-request-backup-payload-fixtures.php';

/**
 * Provides request-backup rendering fixture builders.
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Request_Backup_Rendering_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Request_Backup_History_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Request_Backup_Payload_Fixtures;

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

}
