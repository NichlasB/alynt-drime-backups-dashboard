<?php
/**
 * Event log storage tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/event-log-test-harness.php';

/**
 * Tests structured diagnostics event storage maintenance.
 */
class EventLogStorageTest extends TestCase {
	/**
	 * Resets option shims.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		global $alynt_drime_backups_dashboard_test_options;
		global $alynt_drime_backups_dashboard_test_autoload;
		global $alynt_drime_backups_dashboard_test_unchanged_update_fails;

		$alynt_drime_backups_dashboard_test_options                = array();
		$alynt_drime_backups_dashboard_test_autoload               = array();
		$alynt_drime_backups_dashboard_test_unchanged_update_fails = false;
	}

	/**
	 * Clear removes stored events.
	 *
	 * @return void
	 */
	public function test_clear_removes_events() {
		$log = new Alynt_Drime_Backups_Dashboard_Event_Log();
		$log->update_settings( array( 'enabled' => true ) );
		$log->log( 'critical', 'cron', 'poll_failed', 'Poll failed.' );

		$this->assertNotEmpty( $log->recent_events() );
		$this->assertTrue( $log->clear() );
		$this->assertSame( array(), $log->recent_events() );
	}

	/**
	 * Clear treats an already-empty event buffer as a successful no-op.
	 *
	 * @return void
	 */
	public function test_clear_empty_events_succeeds_as_noop() {
		global $alynt_drime_backups_dashboard_test_options;
		global $alynt_drime_backups_dashboard_test_unchanged_update_fails;

		$alynt_drime_backups_dashboard_test_options[ Alynt_Drime_Backups_Dashboard_Event_Log::OPTION_EVENTS ] = array();
		$alynt_drime_backups_dashboard_test_unchanged_update_fails = true;

		$log = new Alynt_Drime_Backups_Dashboard_Event_Log();

		$this->assertSame( array(), $log->recent_events() );
		$this->assertTrue( $log->clear() );
		$this->assertSame( array(), $log->recent_events() );
	}
}
