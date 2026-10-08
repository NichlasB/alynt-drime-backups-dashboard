<?php
/**
 * Diagnostics restore-readiness aggregate tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/diagnostics-test-bootstrap.php';
require_once __DIR__ . '/support/diagnostics-restore-readiness-fixtures.php';

/**
 * Tests support-safe restore-readiness aggregate diagnostics.
 */
class DiagnosticsRestoreReadinessAggregatesTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Test_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Restore_Readiness_Fixtures;

	/**
	 * Restore-readiness diagnostics are aggregate-only and evidence-only.
	 *
	 * @return void
	 */
	public function test_restore_readiness_diagnostics_are_aggregate_only() {
		$result  = $this->collect_diagnostics( $this->restore_readiness_sites(), $this->restore_readiness_snapshots() );
		$encoded = wp_json_encode( $result['support'] );

		$this->assertSame( 2, $result['counts']['restore_readiness']['reporting_sites'] );
		$this->assertSame( 1, $result['counts']['restore_readiness']['evidence_sites'] );
		$this->assertSame( 1, $result['counts']['restore_readiness']['incomplete_sites'] );
		$this->assertSame( 3, $result['counts']['restore_readiness']['reported_candidates'] );
		$this->assertSame( 1, $result['counts']['restore_readiness']['complete_candidates'] );
		$this->assertSame( 2, $result['counts']['restore_readiness']['incomplete_candidates'] );
		$this->assertSame( 2, $result['counts']['restore_readiness']['server_candidates'] );
		$this->assertSame( 1, $result['counts']['restore_readiness']['server_complete'] );
		$this->assertSame( 1, $result['counts']['restore_readiness']['server_incomplete'] );
		$this->assertSame( 1, $result['counts']['restore_readiness']['wpvivid_candidates'] );
		$this->assertSame( 0, $result['counts']['restore_readiness']['wpvivid_complete'] );
		$this->assertSame( 1, $result['counts']['restore_readiness']['wpvivid_incomplete'] );
		$this->assertStringContainsString( 'restore_readiness', $encoded );
		$this->assertStringContainsString( 'reported_candidates', $encoded );
		$this->assertStringContainsString( 'server_candidates', $encoded );
		$this->assertStringContainsString( 'wpvivid_candidates', $encoded );
		$this->assertStringNotContainsString( 'client1.example.com', $encoded );
		$this->assertStringNotContainsString( 'Client 1', $encoded );
		$this->assertStringNotContainsString( 'opaque-do-not-export', $encoded );
		$this->assertStringNotContainsString( 'another-opaque-ref', $encoded );
	}
}
