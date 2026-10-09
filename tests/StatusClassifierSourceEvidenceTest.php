<?php
/**
 * Status classifier source-evidence tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/status-classifier-test-bootstrap.php';

/**
 * Tests generic backup-source evidence classification.
 */
class StatusClassifierSourceEvidenceTest extends TestCase {
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
	 * Source queue warnings alone stay informational.
	 *
	 * @return void
	 */
	public function test_source_queue_warning_alone_is_working() {
		$result = $this->classify_payload(
			array_merge(
				$this->healthy_payload(),
				array(
					'backup_sources' => array(
						'server' => $this->source_payload(
							array(
								'queued_count'     => 1,
								'freshness_status' => 'fresh',
								'warning_count'    => 1,
								'warnings'         => array(
									array(
										'code'    => 'source_queue_not_empty',
										'message' => 'Queued package waiting to upload.',
									),
								),
							)
						),
					),
				)
			)
		);

		$this->assertSame( 'working', $result['category'] );
	}

	/**
	 * Configured stale source evidence needs attention.
	 *
	 * @return void
	 */
	public function test_stale_backup_source_needs_attention() {
		$result = $this->classify_payload(
			array_merge(
				$this->healthy_payload(),
				array(
					'backup_sources' => array(
						'server' => $this->source_payload(
							array(
								'freshness_status' => 'stale',
							)
						),
					),
				)
			)
		);

		$this->assertSame( 'needs_attention', $result['category'] );
		$this->assertStringContainsString( 'stale or missing upload evidence', $result['message'] );
	}

	/**
	 * Lack of all known sources is not configured.
	 *
	 * @return void
	 */
	public function test_no_known_source_is_not_configured() {
		$result = $this->classify_payload(
			array(
				'schema_version'              => 1,
				'server_outbox_configured'    => false,
				'wpvivid_override_configured' => false,
				'old_wpvivid_uploader_active' => false,
			)
		);

		$this->assertSame( 'not_configured', $result['category'] );
	}

	/**
	 * Source summaries can positively prove no supported source is configured.
	 *
	 * @return void
	 */
	public function test_backup_sources_can_prove_not_configured() {
		$result = $this->classify_payload(
			array_merge(
				$this->healthy_payload(),
				array(
					'server_outbox_configured'    => true,
					'wpvivid_override_configured' => true,
					'backup_sources'              => array(
						'server'  => $this->source_payload(
							array(
								'configured'          => false,
								'has_upload_evidence' => false,
								'freshness_status'    => 'not_configured',
							)
						),
						'wpvivid' => $this->source_payload(
							array(
								'configured'          => false,
								'has_upload_evidence' => false,
								'freshness_status'    => 'not_configured',
							)
						),
					),
				)
			)
		);

		$this->assertSame( 'not_configured', $result['category'] );
	}

}
