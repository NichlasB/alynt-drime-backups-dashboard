<?php
/**
 * Diagnostics remote-action aggregate tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/diagnostics-test-bootstrap.php';
require_once __DIR__ . '/support/diagnostics-schedule-management-fixtures.php';

/**
 * Tests support-safe schedule-management aggregate diagnostics.
 */
class DiagnosticsRemoteActionAggregatesTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Test_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Schedule_Management_Fixtures;

	/**
	 * Schedule-management diagnostics are aggregate-only.
	 *
	 * @return void
	 */
	public function test_schedule_management_diagnostics_are_aggregate_only() {
		$result  = $this->collect_diagnostics( $this->schedule_management_sites(), $this->schedule_management_snapshots() );
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
