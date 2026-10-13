<?php
/**
 * Status classifier external WPvivid failure-policy tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/status-classifier-test-bootstrap.php';

/**
 * Tests that external optional WPvivid policy does not hide failed uploads.
 */
class StatusClassifierWpvividExternalFailurePolicyTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Status_Classifier_Test_Fixtures;

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
