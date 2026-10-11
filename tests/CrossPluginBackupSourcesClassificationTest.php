<?php
/**
 * Cross-plugin backup source status classification tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/includes/traits/trait-status-payload-validator-backup-sources.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-status-payload-validator-sanitizers.php';
require_once dirname( __DIR__ ) . '/includes/class-status-payload-validator.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-status-classifier-backup-sources.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-status-classifier-helpers.php';
require_once dirname( __DIR__ ) . '/includes/class-status-classifier.php';

/**
 * Tests dashboard classification of uploader-produced backup_sources payloads.
 */
class CrossPluginBackupSourcesClassificationTest extends TestCase {
	/**
	 * Queue-only source warnings do not convert fresh source evidence to attention.
	 *
	 * @return void
	 */
	public function test_uploader_fresh_queue_only_fixture_classifies_as_working() {
		$result = $this->classify_contract_payload( $this->validated_fixture_payload() );

		$this->assertSame( 'working', $result['category'] );
	}

	/**
	 * Stale source evidence from the uploader is surfaced as attention.
	 *
	 * @return void
	 */
	public function test_uploader_stale_source_fixture_classifies_as_needs_attention() {
		$payload                                                   = $this->validated_fixture_payload();
		$payload['backup_sources']['server']['freshness_status']   = 'stale';
		$payload['backup_sources']['server']['warning_count']      = 1;
		$payload['backup_sources']['server']['warnings']           = array(
			array(
				'code'    => 'source_latest_upload_stale',
				'message' => 'The latest uploaded backup evidence is older than the default freshness window.',
			),
		);
		$result                                                    = $this->classify_contract_payload( $payload );

		$this->assertSame( 'needs_attention', $result['category'] );
	}

	/**
	 * Loads the uploader-shaped schema-1 fixture.
	 *
	 * @return array<string,mixed>
	 */
	private function fixture_payload() {
		$fixture = file_get_contents( __DIR__ . '/fixtures/uploader-backup-sources-schema1.json' );
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
