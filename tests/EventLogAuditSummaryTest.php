<?php
/**
 * Event log audit summary tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/event-log-test-harness.php';

/**
 * Tests local operator audit summary aggregation.
 */
class EventLogAuditSummaryTest extends TestCase {
	/**
	 * Resets option shims.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		global $alynt_drime_backups_dashboard_test_options;
		global $alynt_drime_backups_dashboard_test_autoload;
		global $alynt_drime_backups_dashboard_test_current_user_id;

		$alynt_drime_backups_dashboard_test_options         = array();
		$alynt_drime_backups_dashboard_test_autoload        = array();
		$alynt_drime_backups_dashboard_test_current_user_id = 42;
	}

	/**
	 * Audit summary reports bounded aggregate counts.
	 *
	 * @return void
	 */
	public function test_audit_summary_counts_actions_and_outcomes() {
		$log = new Alynt_Drime_Backups_Dashboard_Event_Log();

		$log->audit_action( 'check_status_now', 'failed' );
		$log->audit_action( 'check_status_now', 'succeeded' );
		$log->audit_action( 'revoke_local', 'succeeded' );

		$summary = $log->audit_summary();

		$this->assertSame( 3, $summary['total'] );
		$this->assertNotEmpty( $summary['last_action_at'] );
		$this->assertSame( 2, $summary['actions']['check_status_now'] );
		$this->assertSame( 1, $summary['actions']['revoke_local'] );
		$this->assertSame( 1, $summary['outcomes']['failed'] );
		$this->assertSame( 2, $summary['outcomes']['succeeded'] );
	}
}
