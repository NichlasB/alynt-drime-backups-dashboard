<?php
/**
 * Status classifier WPvivid freshness tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/status-classifier-test-bootstrap.php';

/**
 * Tests WPvivid freshness policy windows.
 */
class StatusClassifierWpvividFreshnessTest extends TestCase {
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
	 * WPvivid stale evidence inside the dashboard policy window stays working.
	 *
	 * @return void
	 */
	public function test_wpvivid_stale_inside_dashboard_policy_is_working() {
		$result = $this->classify_wpvivid_stale_payload(
			array(
				'latest_upload_age_seconds' => 172800,
			)
		);

		$this->assertSame( 'working', $result['category'] );
	}

	/**
	 * WPvivid stale evidence outside the dashboard policy window still needs attention.
	 *
	 * @return void
	 */
	public function test_wpvivid_stale_outside_dashboard_policy_needs_attention() {
		$result = $this->classify_wpvivid_stale_payload(
			array(
				'latest_upload_age_seconds' => 1382400,
			)
		);

		$this->assertSame( 'needs_attention', $result['category'] );
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
	 * WPvivid stale evidence outside a detected schedule policy window still needs attention.
	 *
	 * @return void
	 */
	public function test_wpvivid_stale_outside_detected_schedule_policy_needs_attention() {
		$result = $this->classify_wpvivid_stale_payload(
			array(
				'latest_upload_age_seconds' => 900000,
				'schedule_policy'           => array(
					'detected'              => true,
					'basis'                 => 'wpvivid_schedule_setting',
					'recurrence'            => 'wpvivid_weekly',
					'schedule_count'        => 1,
					'interval_seconds'      => 604800,
					'grace_seconds'         => 172800,
					'policy_window_seconds' => 777600,
				),
			)
		);

		$this->assertSame( 'needs_attention', $result['category'] );
	}

	/**
	 * Classifies a healthy payload with stale WPvivid source overrides.
	 *
	 * @param array<string,mixed> $wpvivid_overrides WPvivid source overrides.
	 * @return array<string,mixed>
	 */
	private function classify_wpvivid_stale_payload( array $wpvivid_overrides ) {
		return $this->classifier->classify(
			$this->active_site(),
			$this->snapshot(
				array_merge(
					$this->healthy_payload(),
					array(
						'backup_sources' => array(
							'server'  => $this->source_payload(),
							'wpvivid' => array_merge(
								$this->source_payload(),
								array(
									'source_key'               => 'wpvivid',
									'source_label'             => 'WPvivid',
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
							),
						),
					)
				)
			),
			1700000300
		);
	}

}
