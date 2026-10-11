<?php
/**
 * Status classifier queue and upload-state tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/status-classifier-test-bootstrap.php';

/**
 * Tests queue and failed-upload classification.
 */
class StatusClassifierQueueTest extends TestCase {
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
	 * Failed uploads with a current queue require attention.
	 *
	 * @return void
	 */
	public function test_failed_uploads_need_attention() {
		$result = $this->classify_payload(
			array(
				'schema_version'           => 1,
				'server_outbox_configured' => true,
				'queue_count'              => 1,
				'failed_count'             => 1,
			)
		);

		$this->assertSame( 'needs_attention', $result['category'] );
	}

	/**
	 * Historical failed upload counts do not override healthy current evidence.
	 *
	 * @return void
	 */
	public function test_historical_failed_uploads_without_queue_are_working_when_sources_are_healthy() {
		$result = $this->classify_payload(
			array_merge(
				$this->healthy_payload(),
				array(
					'failed_count'   => 1,
					'backup_sources' => $this->backup_sources_payload(
						array(
							'failed_count'   => 0,
							'queued_count'   => 0,
							'uploaded_count' => 1,
						),
						array(
							'failed_count' => 1,
						),
					),
				)
			)
		);

		$this->assertSame( 'working', $result['category'] );
	}

	/**
	 * Queue alone is not a failure.
	 *
	 * @return void
	 */
	public function test_queue_alone_is_working() {
		$result = $this->classify_payload(
			array(
				'schema_version'           => 1,
				'server_outbox_configured' => true,
				'queue_count'              => 3,
				'active_upload'            => true,
				'failed_count'             => 0,
				'warning_count'            => 0,
			)
		);

		$this->assertSame( 'working', $result['category'] );
	}
}
