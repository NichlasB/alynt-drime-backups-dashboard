<?php
/**
 * Diagnostics cleanup-preview aggregate tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/diagnostics-test-bootstrap.php';
require_once __DIR__ . '/support/diagnostics-cleanup-preview-fixtures.php';

/**
 * Tests support-safe cleanup-preview aggregate diagnostics.
 */
class DiagnosticsCleanupPreviewAggregatesTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Test_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Cleanup_Preview_Fixtures;

	/**
	 * Cleanup-preview diagnostics are aggregate-only.
	 *
	 * @return void
	 */
	public function test_cleanup_preview_diagnostics_are_aggregate_only() {
		$result  = $this->collect_diagnostics( $this->cleanup_preview_sites(), $this->cleanup_preview_snapshots() );
		$encoded = wp_json_encode( $result['support'] );

		$this->assertSame( 2, $result['counts']['cleanup_preview']['reporting_sites'] );
		$this->assertSame( 1, $result['counts']['cleanup_preview']['preview_supported_sites'] );
		$this->assertSame( 1, $result['counts']['cleanup_preview']['unavailable_sites'] );
		$this->assertSame( 1, $result['counts']['cleanup_preview']['apply_or_mutation_advertised_sites'] );
		$this->assertSame( 2, $result['counts']['cleanup_preview']['supported_categories'] );
		$this->assertStringContainsString( 'cleanup_preview', $encoded );
		$this->assertStringContainsString( 'preview_supported_sites', $encoded );
		$this->assertStringNotContainsString( 'client1.example.com', $encoded );
		$this->assertStringNotContainsString( 'Client 1', $encoded );
		$this->assertStringNotContainsString( 'uploader_temp_artifacts', $encoded );
	}
}
