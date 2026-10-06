<?php
/**
 * Diagnostics backup-source aggregate tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/diagnostics-test-bootstrap.php';

/**
 * Tests backup-source diagnostics aggregates.
 */
class DiagnosticsBackupSourceAggregatesTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Test_Fixtures;

	/**
	 * Backup source diagnostics are aggregate-only.
	 *
	 * @return void
	 */
	public function test_backup_source_diagnostics_are_aggregate_only() {
		$result = $this->collect_diagnostics(
			array(
				$this->site( 1 ),
			),
			array(
				1 => $this->snapshot(
					array(
						'backup_sources' => array(
							'server'  => array(
								'freshness_status' => 'stale',
							),
							'wpvivid' => array(
								'freshness_status' => 'no_upload_evidence',
							),
						),
					)
				),
			)
		);

		$encoded = wp_json_encode( $result['support'] );

		$this->assertSame( 1, $result['counts']['backup_sources']['reporting_sites'] );
		$this->assertSame( 1, $result['counts']['backup_sources']['stale_sources'] );
		$this->assertSame( 1, $result['counts']['backup_sources']['no_upload_evidence_sources'] );
		$this->assertStringContainsString( 'backup_sources', $encoded );
		$this->assertStringNotContainsString( 'client1.example.com', $encoded );
		$this->assertStringNotContainsString( 'Client 1', $encoded );
	}
}
