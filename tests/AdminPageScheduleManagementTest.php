<?php
/**
 * Admin schedule-management preview rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-schedule-management-test-harness.php';

/**
 * Tests read-only schedule-management preview rendering.
 */
class AdminPageScheduleManagementTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Schedule_Management_Test_Fixtures;

	/**
	 * Site Detail renders preview-only schedule capability without mutation controls.
	 *
	 * @return void
	 */
	public function test_schedule_preview_panel_renders_without_apply_controls() {
		$harness = new Alynt_Drime_Backups_Dashboard_Schedule_Management_Test_Harness();
		$html    = $harness->panel_html( $this->payload() );

		$this->assertStringContainsString( 'Schedule Management', $html );
		$this->assertStringContainsString( 'Preview only', $html );
		$this->assertStringContainsString( 'Alynt scan/upload', $html );
		$this->assertStringContainsString( 'every 15 minutes', $html );
		$this->assertStringContainsString( '15 minutes', $html );
		$this->assertStringContainsString( 'Not enabled on the client', $html );
		$this->assertStringContainsString( 'Execution unavailable; rollback apply is not available in this release.', $html );
		$this->assertStringContainsString( 'adbd-status-pill is-hidden', $html );
		$this->assertStringContainsString( '>Hidden</span> Hidden until the latest client report advertises rollback-preview support.', $html );
		$this->assertStringContainsString( 'Hidden until the latest client report advertises rollback-preview support.', $html );
		$this->assertStringContainsString( 'Rollback execution remains unavailable in this release', $html );
		$this->assertStringContainsString( 'Preview Schedule Change', $html );
		$this->assertStringContainsString( 'every 30 minutes', $html );
		$this->assertStringContainsString( '<form', $html );
		$this->assertStringNotContainsString( 'schedule_apply', $html );
		$this->assertStringNotContainsString( 'schedule_rollback', $html );
	}

	/**
	 * Apply-capable schedule management shows apply only for a fresh preview.
	 *
	 * @return void
	 */
	public function test_schedule_apply_form_requires_fresh_preview() {
		$harness                 = new Alynt_Drime_Backups_Dashboard_Schedule_Management_Test_Harness();
		$harness->remote_actions = new Alynt_Drime_Backups_Dashboard_Schedule_Management_Test_Actions();
		$html                    = $harness->panel_html( $this->payload( true ) );

		$this->assertStringContainsString( 'Apply available after preview', $html );
		$this->assertStringContainsString( 'Apply Previewed Schedule Change', $html );
		$this->assertStringContainsString( 'schedule_apply_confirm', $html );
		$this->assertStringContainsString( 'from every 15 minutes to every 30 minutes', $html );
		$this->assertStringContainsString( 'future scan scheduling for the Alynt uploader only', $html );
		$this->assertStringContainsString( 'upload worker may keep its own cadence', $html );
		$this->assertStringContainsString( 'rollback is not available in this release', $html );
		$this->assertStringContainsString( 'rollback is unavailable', $html );
		$this->assertStringNotContainsString( 'future scan/upload timing only', $html );
		$this->assertStringNotContainsString( 'schedule_rollback', $html );
	}

	/**
	 * Missing capability renders a clear no-data state.
	 *
	 * @return void
	 */
	public function test_missing_schedule_preview_reports_not_reported() {
		$harness = new Alynt_Drime_Backups_Dashboard_Schedule_Management_Test_Harness();
		$html    = $harness->panel_html( array() );

		$this->assertStringContainsString( 'Not reported yet', $html );
		$this->assertStringNotContainsString( '<form', $html );
	}

}
