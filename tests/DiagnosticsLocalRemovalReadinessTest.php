<?php
/**
 * Diagnostics local-removal readiness tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/diagnostics-test-bootstrap.php';
require_once __DIR__ . '/support/diagnostics-local-removal-fixtures.php';

/**
 * Tests support-safe diagnostics counts for retained local-record removal readiness.
 */
class DiagnosticsLocalRemovalReadinessTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Test_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Local_Removal_Fixtures;

	/**
	 * Diagnostics expose aggregate local removal-readiness evidence for archived records.
	 *
	 * @return void
	 */
	public function test_local_removal_readiness_counts_archived_records() {
		$result = $this->collect_diagnostics(
			$this->local_removal_sites(),
			array(),
			$this->local_removal_histories(),
			$this->local_removal_action_counts(),
			$this->local_removal_non_terminal_action_counts()
		);

		$local_removal = $result['counts']['local_removal'];
		$encoded       = wp_json_encode( $result['support'] );

		$this->assertSame( 3, $local_removal['archived_records'] );
		$this->assertSame( 1, $local_removal['ready_records'] );
		$this->assertSame( 2, $local_removal['blocked_records'] );
		$this->assertSame( 3, $local_removal['retained_snapshot_rows'] );
		$this->assertSame( 9, $local_removal['retained_action_rows'] );
		$this->assertSame( 1, $local_removal['non_terminal_action_rows'] );
		$this->assertStringContainsString( 'local_removal', $encoded );
		$this->assertStringNotContainsString( 'Client 1', $encoded );
		$this->assertStringNotContainsString( 'client1.example.com', $encoded );
	}
}
