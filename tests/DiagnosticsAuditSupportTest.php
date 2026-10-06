<?php
/**
 * Diagnostics audit-history support tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/event-log-test-harness.php';
require_once __DIR__ . '/support/diagnostics-test-bootstrap.php';

/**
 * Tests support-safe audit-history diagnostics.
 */
class DiagnosticsAuditSupportTest extends TestCase {
	/**
	 * Resets option shims.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		global $alynt_drime_backups_dashboard_test_options;
		global $alynt_drime_backups_dashboard_test_autoload;

		$alynt_drime_backups_dashboard_test_options  = array();
		$alynt_drime_backups_dashboard_test_autoload = array();
	}

	/**
	 * Audit summary includes aggregates without leaking event context.
	 *
	 * @return void
	 */
	public function test_support_summary_includes_audit_aggregate_only() {
		$event_log = new Alynt_Drime_Backups_Dashboard_Event_Log();
		$event_log->audit_action(
			'check_status_now',
			'succeeded',
			array(
				'dashboard_site_id' => 9,
				'pairing_token'     => 'adb1.secret-token',
			)
		);

		$diagnostics = new Alynt_Drime_Backups_Dashboard_Diagnostics(
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Site_Repository( array() ),
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Snapshot_Repository( array() ),
			new Alynt_Drime_Backups_Dashboard_Status_Classifier(),
			null,
			$event_log
		);

		$result  = $diagnostics->collect();
		$encoded = wp_json_encode( $result['support'] );

		$this->assertNotFalse( $encoded );
		$this->assertStringContainsString( 'audit_history', $encoded );
		$this->assertSame( 1, $result['support']['logging']['audit_history']['event_count'] );
		$this->assertSame( 90, $result['support']['logging']['audit_history']['retention_days'] );
		$this->assertSame( 500, $result['support']['logging']['audit_history']['max_events'] );
		$this->assertStringNotContainsString( 'adb1.secret-token', $encoded );
		$this->assertStringNotContainsString( 'dashboard_site_id', $encoded );
	}
}
