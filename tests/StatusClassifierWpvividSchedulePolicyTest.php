<?php
/**
 * Status classifier WPvivid schedule-policy tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/status-classifier-test-bootstrap.php';

/**
 * Tests WPvivid detected schedule freshness policy windows.
 */
class StatusClassifierWpvividSchedulePolicyTest extends TestCase {
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
	 * WPvivid stale evidence inside a detected schedule policy window stays working.
	 *
	 * @return void
	 */
	public function test_wpvivid_stale_inside_detected_schedule_policy_is_working() {
		$result = $this->classify_wpvivid_stale_payload(
			array(
				'latest_upload_age_seconds' => 2500000,
				'schedule_policy'           => array(
					'detected'              => true,
					'basis'                 => 'wpvivid_schedule_setting',
					'recurrence'            => 'wpvivid_monthly',
					'schedule_count'        => 1,
					'interval_seconds'      => 2592000,
					'grace_seconds'         => 259200,
					'policy_window_seconds' => 2851200,
				),
			)
		);

		$this->assertSame( 'working', $result['category'] );
	}

	/**
	 * Classifies a healthy payload with stale WPvivid source overrides.
	 *
	 * @param array<string,mixed> $wpvivid_overrides WPvivid source overrides.
	 * @return array<string,mixed>
	 */
	private function classify_wpvivid_stale_payload( array $wpvivid_overrides ) {
		return $this->classify_payload(
			array_merge(
				$this->healthy_payload(),
				array(
					'backup_sources' => $this->backup_sources_payload(
						array_merge(
							array(
								'freshness_status'         => 'stale',
								'freshness_window_seconds' => 129600,
								'warnings'                 => array(
									array(
										'code'    => 'source_latest_upload_stale',
										'message' => 'The latest uploaded backup evidence is older than the default freshness window.',
									),
								),
							),
							$wpvivid_overrides
						)
					),
				)
			)
		);
	}
}
