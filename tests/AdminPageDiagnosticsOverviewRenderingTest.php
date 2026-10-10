<?php
/**
 * Admin diagnostics overview rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-diagnostics-rendering-test-harness.php';
require_once __DIR__ . '/support/admin-page-diagnostics-overview-fixtures.php';

/**
 * Tests diagnostics overview rendering.
 */
class AdminPageDiagnosticsOverviewRenderingTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Overview_Test_Fixtures;

	/**
	 * Site-polling diagnostics explain non-polling record states.
	 *
	 * @return void
	 */
	public function test_overview_renders_record_state_polling_summary() {
		$harness = new Alynt_Drime_Backups_Dashboard_Diagnostics_Overview_Test_Harness();
		$html    = $harness->overview_html( $this->record_state_polling_summary_diagnostics() );

		$this->assertStringContainsString( 'Total dashboard records', $html );
		$this->assertStringContainsString( 'Records not currently polling', $html );
		$this->assertStringContainsString( 'Pending pairing records', $html );
		$this->assertStringContainsString( 'Locally revoked records', $html );
		$this->assertStringContainsString( 'Archived local records', $html );
		$this->assertStringContainsString( 'Attention / Recovery History', $html );
		$this->assertStringContainsString( 'Repeated attention seen', $html );
		$this->assertStringContainsString( 'Recently recovered records', $html );
		$this->assertStringContainsString( 'Repeated attention records', $html );
		$this->assertStringContainsString( 'Recent attention transitions', $html );
		$this->assertStringContainsString( 'These aggregate counts come from retained redacted snapshot status history only.', $html );
		$this->assertStringContainsString( 'Restore Readiness Evidence', $html );
		$this->assertStringContainsString( 'Mixed restore evidence across reporting sites', $html );
		$this->assertStringContainsString( 'Sites reporting evidence', $html );
		$this->assertStringContainsString( 'Reported source candidates', $html );
		$this->assertStringContainsString( 'Server runner candidates', $html );
		$this->assertStringContainsString( 'WPvivid candidates', $html );
		$this->assertStringContainsString( '14 reported · 8 complete · 6 incomplete', $html );
		$this->assertStringContainsString( '14 reported · 0 complete · 14 incomplete', $html );
		$this->assertStringContainsString( 'they are not a restore guarantee', $html );
		$this->assertStringContainsString( 'Local Removal Readiness', $html );
		$this->assertStringContainsString( 'Archived records evaluated', $html );
		$this->assertStringContainsString( 'Future-removal ready records', $html );
		$this->assertStringContainsString( 'Blocked retained records', $html );
		$this->assertStringContainsString( 'Retained snapshot rows', $html );
		$this->assertStringContainsString( 'Retained action history rows', $html );
		$this->assertStringContainsString( 'Non-terminal action rows', $html );
		$this->assertStringContainsString( 'Permanent local removal is still unavailable', $html );
		$this->assertStringContainsString( '<td>28</td>', $html );
		$this->assertStringContainsString( '<td>3</td>', $html );
	}

	/**
	 * Diagnostics overview shows generated-at evidence and a cache-busted refresh link.
	 *
	 * @return void
	 */
	public function test_overview_renders_diagnostics_freshness_notice() {
		$harness = new Alynt_Drime_Backups_Dashboard_Diagnostics_Overview_Test_Harness();
		$html    = $harness->overview_html( $this->freshness_notice_diagnostics() );

		$this->assertStringContainsString( 'Generated at 2026-09-19 10:53:22 UTC.', $html );
		$this->assertStringContainsString( 'Refresh diagnostics', $html );
		$this->assertStringContainsString( 'tab=diagnostics', $html );
		$this->assertStringContainsString( '_adbd_check=20260919105322', $html );
	}

	/**
	 * Diagnostics overview shows support-safe runtime identity evidence.
	 *
	 * @return void
	 */
	public function test_overview_renders_dashboard_runtime_identity() {
		$harness = new Alynt_Drime_Backups_Dashboard_Diagnostics_Overview_Test_Harness();
		$html    = $harness->overview_html( $this->runtime_identity_diagnostics() );

		$this->assertStringContainsString( 'Dashboard Runtime', $html );
		$this->assertStringContainsString( 'Installed version', $html );
		$this->assertStringContainsString( ALYNT_DRIME_BACKUPS_DASHBOARD_VERSION, $html );
		$this->assertStringContainsString( 'Protocol v1 / Status schema v1', $html );
		$this->assertStringContainsString( 'Remote-action boundary', $html );
		$this->assertStringContainsString( 'Only explicitly opted-in signed client actions are available', $html );
		$this->assertStringContainsString( 'the dashboard stores no Drime API credentials', $html );
		$this->assertStringContainsString( 'Capability gate', $html );
		$this->assertStringContainsString( 'controls appear only when the latest client report advertises', $html );
		$this->assertStringContainsString( 'Diagnostics boundary', $html );
		$this->assertStringContainsString( 'does not poll clients, run remote actions, change schedules, or mutate backups', $html );
		$this->assertStringContainsString( 'Deployment boundary', $html );
		$this->assertStringContainsString( 'live-site changes stay behind the separate deployment approval gate', $html );
		$this->assertStringContainsString( 'support-safe identity check', $html );
	}
}
