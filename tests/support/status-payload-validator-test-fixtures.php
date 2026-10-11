<?php
/**
 * Status payload validator test fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/status-payload-validator-payload-fixtures.php';

/**
 * Shared status payload validator fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Status_Payload_Validator_Test_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Status_Payload_Validator_Backup_Source_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Status_Payload_Validator_Payload_Fixtures;

	/**
	 * Creates a fresh validator under test.
	 *
	 * @return Alynt_Drime_Backups_Dashboard_Status_Payload_Validator
	 */
	private function status_payload_validator() {
		return new Alynt_Drime_Backups_Dashboard_Status_Payload_Validator();
	}

	/**
	 * Validates a payload with the default fixture site UUID.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @return array<string,mixed>|WP_Error
	 */
	private function validate_payload( array $payload ) {
		return $this->validate_payload_for_site_uuid(
			$payload,
			'11111111-1111-4111-8111-111111111111'
		);
	}

	/**
	 * Validates a payload with a supplied fixture site UUID.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @param string              $site_uuid Site UUID.
	 * @return array<string,mixed>|WP_Error
	 */
	private function validate_payload_for_site_uuid( array $payload, $site_uuid ) {
		return $this->status_payload_validator()->validate(
			$payload,
			$site_uuid
		);
	}
}
