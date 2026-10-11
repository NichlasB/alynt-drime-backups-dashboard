<?php
/**
 * Status payload validator backup source fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/status-payload-validator-source-fixtures.php';

/**
 * Shared status payload validator backup source fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Status_Payload_Validator_Backup_Source_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Status_Payload_Validator_Source_Fixtures;

	/**
	 * Creates a backup sources payload with server and WPvivid entries.
	 *
	 * @param array<string,mixed> $wpvivid_overrides WPvivid source overrides.
	 * @param array<string,mixed> $server_overrides Server source overrides.
	 * @return array<string,array<string,mixed>>
	 */
	private function backup_sources_payload( array $wpvivid_overrides = array(), array $server_overrides = array() ) {
		return array(
			'server'  => $this->source_payload( $server_overrides ),
			'wpvivid' => $this->wpvivid_source_payload( $wpvivid_overrides ),
		);
	}

	/**
	 * Creates a WPvivid backup source payload.
	 *
	 * @param array<string,mixed> $overrides Source overrides.
	 * @return array<string,mixed>
	 */
	private function wpvivid_source_payload( array $overrides = array() ) {
		return $this->source_payload(
			array_merge(
				array(
					'source_key' => 'wpvivid',
				),
				$overrides
			)
		);
	}
}
