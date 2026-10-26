<?php
/**
 * Event-export encoding failure and recovery tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-diagnostics-rendering-test-harness.php';
require_once __DIR__ . '/support/admin-page-sites-list-wordpress-shims.php';

/** Tests actual rendering/encoding, never WordPress storage operations. */
class AdminPageDiagnosticsEventExportTest extends TestCase {
	/**
	 * A failed export must not offer fake empty JSON, but keeps event review.
	 *
	 * @return void
	 */
	public function test_failed_export_is_not_offered_as_an_empty_success() {
		$harness = new Alynt_Drime_Backups_Dashboard_Diagnostics_Rendering_Test_Harness();
		$html    = $harness->event_export_html( array( array( 'context' => array( 'count' => INF ) ) ) );
		$this->assertStringContainsString( 'role="alert"', $html );
		$this->assertStringContainsString( 'refreshing the page', $html );
		$this->assertStringNotContainsString( 'id="adbd-event-export"', $html );
		$this->assertStringNotContainsString( 'data-copy-target="adbd-event-export"', $html );
		$this->assertStringContainsString( 'Recent diagnostic events', $html );
		$this->assertStringContainsString( 'Review Clearing Diagnostics Events', $html );
	}

	/**
	 * A valid empty export stays available and escaped populated content survives.
	 *
	 * @return void
	 */
	public function test_valid_exports_remain_available() {
		$harness = new Alynt_Drime_Backups_Dashboard_Diagnostics_Rendering_Test_Harness();
		$empty   = $harness->event_export_html( array() );
		$this->assertStringContainsString( '>[]</textarea>', $empty );
		$this->assertStringContainsString( 'data-copy-target="adbd-event-export"', $empty );
		$html = $harness->event_export_html( array( array( 'message' => '<safe>' ) ) );
		$this->assertStringContainsString( '&lt;safe&gt;', $html );
		$this->assertStringNotContainsString( '<safe>', $html );
	}
}
