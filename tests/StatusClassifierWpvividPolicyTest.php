<?php
/**
 * Status classifier WPvivid policy tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/status-classifier-test-bootstrap.php';

/**
 * Tests WPvivid upload-evidence and source-policy behavior.
 */
class StatusClassifierWpvividPolicyTest extends TestCase {
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
	 * Missing WPvivid upload evidence still needs attention when the source is configured.
	 *
	 * @return void
	 */
	public function test_configured_wpvivid_without_upload_evidence_needs_attention() {
		$result = $this->classify_payload(
			array_merge(
				$this->healthy_payload(),
				array(
					'backup_sources' => $this->backup_sources_payload(
						array(
							'has_upload_evidence'    => false,
							'freshness_status'       => 'no_upload_evidence',
							'latest_uploaded_at'     => 0,
							'latest_inventory_count' => 0,
						)
					),
				)
			)
		);

		$this->assertSame( 'needs_attention', $result['category'] );
	}

}
