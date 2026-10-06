<?php
/**
 * Status classifier tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/status-classifier-test-bootstrap.php';

/**
 * Tests baseline dashboard status classification.
 */
class StatusClassifierTest extends TestCase {
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
	 * Pending sites stay pending before snapshots exist.
	 *
	 * @return void
	 */
	public function test_pending_site_stays_pending() {
		$result = $this->classifier->classify(
			array(
				'status'    => 'pending',
				'paused_at' => null,
			),
			null,
			1234567890
		);

		$this->assertSame( 'pending', $result['category'] );
	}

	/**
	 * Unsupported schema versions are incompatible.
	 *
	 * @return void
	 */
	public function test_unsupported_schema_is_incompatible() {
		$result = $this->classify_payload(
			array(
				'schema_version'              => 2,
				'server_outbox_configured'    => true,
				'wpvivid_override_configured' => false,
			)
		);

		$this->assertSame( 'incompatible', $result['category'] );
	}

	/**
	 * Old snapshots become not reporting.
	 *
	 * @return void
	 */
	public function test_stale_snapshot_is_not_reporting() {
		$result = $this->classifier->classify(
			$this->active_site( '2023-11-14 22:13:20' ),
			$this->snapshot(
				array(
					'schema_version'           => 1,
					'server_outbox_configured' => true,
				),
				'2023-11-14 22:13:20'
			),
			strtotime( '2023-11-14 23:30:00' )
		);

		$this->assertSame( 'not_reporting', $result['category'] );
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
					'backup_sources' => array(
						'server'  => array_merge(
							$this->source_payload(),
							array(
								'failed_count' => 1,
							)
						),
						'wpvivid' => array_merge(
							$this->source_payload(),
							array(
								'source_key'     => 'wpvivid',
								'source_label'   => 'WPvivid',
								'failed_count'   => 0,
								'queued_count'   => 0,
								'uploaded_count' => 1,
							)
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

	/**
	 * Classifies an active-site snapshot payload at the default fixture time.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @return array<string,mixed>
	 */
	private function classify_payload( array $payload ) {
		return $this->classifier->classify(
			$this->active_site(),
			$this->snapshot( $payload ),
			1700000300
		);
	}

}
