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

	/**
	 * External optional WPvivid policy suppresses upload-evidence attention.
	 *
	 * @return void
	 */
	public function test_external_optional_wpvivid_missing_upload_evidence_is_working() {
		$classifier = $this->external_optional_classifier();

		$result = $classifier->classify(
			array_merge(
				$this->active_site(),
				array(
					'id' => 12,
				)
			),
			$this->snapshot(
				array_merge(
					$this->healthy_payload(),
					array(
						'backup_sources' => $this->backup_sources_payload(
							array(
								'has_upload_evidence'    => false,
								'freshness_status'       => 'no_upload_evidence',
								'latest_uploaded_at'     => 0,
								'latest_inventory_count' => 0,
								'warning_count'          => 1,
								'warnings'                => array(
									array(
										'code'    => 'source_no_upload_evidence',
										'message' => 'No uploaded backup evidence has been recorded for this source.',
									),
								),
							)
						),
					)
				)
			),
			1700000300
		);

		$this->assertSame( 'working', $result['category'] );
	}

	/**
	 * External optional WPvivid policy does not hide failed source uploads.
	 *
	 * @return void
	 */
	public function test_external_optional_wpvivid_failed_upload_still_needs_attention() {
		$classifier = $this->external_optional_classifier();

		$result = $classifier->classify(
			array_merge(
				$this->active_site(),
				array(
					'id' => 12,
				)
			),
			$this->snapshot(
				array_merge(
					$this->healthy_payload(),
					array(
						'backup_sources' => $this->backup_sources_payload(
							array(
								'queued_count'     => 1,
								'failed_count'     => 1,
								'freshness_status' => 'no_upload_evidence',
							)
						),
					)
				)
			),
			1700000300
		);

		$this->assertSame( 'needs_attention', $result['category'] );
	}

	/**
	 * Builds a classifier with WPvivid marked as external optional for site 12.
	 *
	 * @return Alynt_Drime_Backups_Dashboard_Status_Classifier
	 */
	private function external_optional_classifier() {
		return new Alynt_Drime_Backups_Dashboard_Status_Classifier(
			new Alynt_Drime_Backups_Dashboard_Source_Policy(
				array(
					'12' => array(
						'wpvivid' => 'external_optional',
					),
				)
			)
		);
	}

}
