<?php
/**
 * Status classifier payload decoding tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/status-classifier-test-bootstrap.php';

/**
 * Tests stored payload decoding and fail-closed classification.
 */
class StatusClassifierPayloadTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Status_Classifier_Test_Fixtures;

	/**
	 * Classifier under test.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Status_Classifier
	 */
	private $classifier;

	/**
	 * Sets up the test.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		$this->classifier = new Alynt_Drime_Backups_Dashboard_Status_Classifier();
	}

	/**
	 * New snapshot schema payload_json is decoded for classification.
	 *
	 * @return void
	 */
	public function test_payload_json_snapshot_is_decoded() {
		$result = $this->classify_snapshot(
			array(
				'schema_version' => 1,
				'payload_json'   => wp_json_encode(
					array(
						'schema_version'           => 1,
						'server_outbox_configured' => true,
						'queue_count'              => 1,
						'failed_count'             => 1,
					)
				),
				'observed_at'    => '2023-11-14 22:15:00',
			)
		);

		$this->assertSame( 'needs_attention', $result['category'] );
	}

	/**
	 * Corrupted stored payload JSON is not treated as healthy or merely unconfigured.
	 *
	 * @return void
	 */
	public function test_malformed_payload_json_is_not_reporting() {
		$result = $this->classify_snapshot(
			array(
				'schema_version' => 1,
				'payload_json'   => '{"schema_version":1,',
				'observed_at'    => '2023-11-14 22:15:00',
			)
		);

		$this->assertSame( 'not_reporting', $result['category'] );
		$this->assertStringContainsString( 'could not be decoded', $result['message'] );
	}

	/**
	 * Empty decoded snapshot payloads fail closed.
	 *
	 * @return void
	 */
	public function test_empty_decoded_payload_is_not_reporting() {
		$result = $this->classify_payload( array() );

		$this->assertSame( 'not_reporting', $result['category'] );
		$this->assertStringContainsString( 'could not be decoded', $result['message'] );
	}
}
