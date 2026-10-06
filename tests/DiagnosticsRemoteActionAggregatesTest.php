<?php
/**
 * Diagnostics remote-action aggregate tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/diagnostics-test-bootstrap.php';

/**
 * Tests support-safe schedule-management aggregate diagnostics.
 */
class DiagnosticsRemoteActionAggregatesTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Test_Fixtures;

	/**
	 * Schedule-management diagnostics are aggregate-only.
	 *
	 * @return void
	 */
	public function test_schedule_management_diagnostics_are_aggregate_only() {
		$diagnostics = new Alynt_Drime_Backups_Dashboard_Diagnostics(
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Site_Repository(
				array(
					$this->site( 1 ),
					$this->site( 2 ),
					$this->site( 3 ),
				)
			),
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Snapshot_Repository(
				array(
					1 => $this->snapshot(
						array(
							'remote_actions' => array(
								'protocol_version'    => 2,
								'enabled'             => true,
								'schedule_management' => array(
									'protocol_version'   => 2,
									'capability_version' => 1,
									'enabled'            => true,
									'preview_only'       => true,
									'apply_supported'    => false,
									'rollback_supported' => false,
									'schedules'          => array(
										array(
											'schedule_id'       => 'alynt_scan_upload',
											'current_cadence'   => 'every_15_minutes',
										),
									),
								),
							),
						)
					),
					2 => $this->snapshot(),
					3 => $this->snapshot(
						array(
							'remote_actions' => array(
								'protocol_version'    => 2,
								'enabled'             => true,
								'schedule_management' => array(
									'protocol_version'            => 2,
									'capability_version'          => 1,
									'enabled'                     => true,
									'preview_only'                => false,
									'apply_supported'             => true,
									'rollback_preview_supported'  => true,
									'rollback_supported'          => false,
									'schedules'                   => array(
										array(
											'schedule_id'                => 'alynt_scan_upload',
											'current_cadence'            => 'every_30_minutes',
											'rollback_preview_supported' => true,
										),
									),
								),
							),
						)
					),
				)
			),
			new Alynt_Drime_Backups_Dashboard_Status_Classifier()
		);

		$result  = $diagnostics->collect();
		$encoded = wp_json_encode( $result['support'] );

		$this->assertSame( 2, $result['counts']['schedule_management']['reporting_sites'] );
		$this->assertSame( 1, $result['counts']['schedule_management']['preview_only_sites'] );
		$this->assertSame( 1, $result['counts']['schedule_management']['apply_sites'] );
		$this->assertSame( 1, $result['counts']['schedule_management']['unavailable_sites'] );
		$this->assertSame( 2, $result['counts']['schedule_management']['reported_schedules'] );
		$this->assertSame( 1, $result['counts']['schedule_management']['rollback_preview_supported_sites'] );
		$this->assertSame( 1, $result['counts']['schedule_management']['rollback_preview_hidden_sites'] );
		$this->assertSame( 0, $result['counts']['schedule_management']['rollback_apply_advertised_sites'] );
		$this->assertStringContainsString( 'schedule_management', $encoded );
		$this->assertStringContainsString( 'rollback_preview_supported_sites', $encoded );
		$this->assertStringNotContainsString( 'client1.example.com', $encoded );
		$this->assertStringNotContainsString( 'Client 1', $encoded );
		$this->assertStringNotContainsString( 'alynt_scan_upload', $encoded );
	}

}
