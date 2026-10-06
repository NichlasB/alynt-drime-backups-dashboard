<?php
/**
 * Admin schedule-management row hint rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-schedule-management-test-harness.php';

/**
 * Tests compact schedule-management row hints.
 */
class AdminPageScheduleRowHintTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Schedule_Management_Test_Fixtures;

	/**
	 * Sites list can show a compact schedule-preview hint.
	 *
	 * @return void
	 */
	public function test_schedule_preview_row_hint_is_compact() {
		$harness = new Alynt_Drime_Backups_Dashboard_Schedule_Management_Test_Harness();
		$html    = $harness->row_hint_html( $this->payload() );

		$this->assertStringContainsString( 'Schedule: preview only', $html );
		$this->assertStringContainsString( 'Alynt scan/upload every 15 minutes', $html );
		$this->assertStringNotContainsString( '<form', $html );
	}

	/**
	 * Sites list distinguishes apply-capable schedule hints without rendering controls.
	 *
	 * @return void
	 */
	public function test_schedule_apply_capable_row_hint_is_display_only() {
		$harness = new Alynt_Drime_Backups_Dashboard_Schedule_Management_Test_Harness();
		$html    = $harness->row_hint_html( $this->payload( true ) );

		$this->assertStringContainsString( 'Schedule: apply gated', $html );
		$this->assertStringContainsString( 'Alynt scan/upload every 15 minutes', $html );
		$this->assertStringNotContainsString( '<form', $html );
		$this->assertStringNotContainsString( 'Preview Schedule Change', $html );
		$this->assertStringNotContainsString( 'Apply Previewed Schedule Change', $html );
	}

	/**
	 * Sites list omits schedule hints when no capability is reported.
	 *
	 * @return void
	 */
	public function test_missing_schedule_capability_renders_no_row_hint() {
		$harness = new Alynt_Drime_Backups_Dashboard_Schedule_Management_Test_Harness();

		$this->assertSame( '', $harness->row_hint_html( array() ) );
	}
}
