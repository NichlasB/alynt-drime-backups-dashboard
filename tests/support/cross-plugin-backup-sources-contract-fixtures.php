<?php
/**
 * Cross-plugin backup-source contract fixture helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared helpers for uploader-produced backup_sources contract tests.
 */
trait Alynt_Drime_Backups_Dashboard_Cross_Plugin_Backup_Sources_Contract_Fixtures {
	/**
	 * Loads the uploader-shaped schema-1 fixture.
	 *
	 * @return array<string,mixed>
	 */
	private function fixture_payload() {
		$fixture = file_get_contents( dirname( __DIR__ ) . '/fixtures/uploader-backup-sources-schema1.json' );
		$payload = json_decode( (string) $fixture, true );

		$this->assertIsArray( $payload );

		return $payload;
	}

	/**
	 * Loads and validates the uploader-shaped schema-1 fixture.
	 *
	 * @return array<string,mixed>
	 */
	private function validated_fixture_payload() {
		$validator = new Alynt_Drime_Backups_Dashboard_Status_Payload_Validator();
		$payload   = $this->fixture_payload();
		$result    = $validator->validate( $payload, (string) $payload['site_uuid'] );

		$this->assertIsArray( $result );

		return $result;
	}

	/**
	 * Classifies a validated contract payload at the current fixture time.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @return array<string,mixed>
	 */
	private function classify_contract_payload( array $payload ) {
		$now        = time();
		$classifier = new Alynt_Drime_Backups_Dashboard_Status_Classifier();

		return $classifier->classify(
			array(
				'status'       => 'active',
				'last_seen_at' => $now,
			),
			array(
				'schema_version'  => 1,
				'observed_at'     => $now,
				'decoded_payload' => $payload,
			),
			$now
		);
	}
}
