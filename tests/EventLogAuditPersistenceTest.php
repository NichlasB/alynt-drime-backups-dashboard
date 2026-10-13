<?php
/**
 * Event log audit-history persistence tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/event-log-test-harness.php';

/**
 * Tests audit-history persistence boundaries.
 */
class EventLogAuditPersistenceTest extends TestCase {
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
	 * Clearing optional diagnostics does not clear the separate audit history.
	 *
	 * @return void
	 */
	public function test_clear_diagnostics_events_preserves_audit_history() {
		$log = new Alynt_Drime_Backups_Dashboard_Event_Log();
		$log->update_settings( array( 'enabled' => true ) );
		$log->log( 'critical', 'cron', 'poll_failed', 'Poll failed.' );
		$log->audit_action( 'clear_diagnostics_events', 'succeeded' );

		$this->assertNotEmpty( $log->recent_events() );
		$this->assertNotEmpty( $log->recent_audit_events() );

		$this->assertTrue( $log->clear() );

		$this->assertSame( array(), $log->recent_events() );
		$this->assertCount( 1, $log->recent_audit_events() );
	}
}
