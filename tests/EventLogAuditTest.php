<?php
/**
 * Event log audit-history tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

if ( ! function_exists( 'get_option' ) ) {
	/**
	 * Test get_option shim.
	 *
	 * @param string $option Option.
	 * @param mixed  $default Default.
	 * @return mixed
	 */
	function get_option( $option, $default = false ) {
		global $alynt_drime_backups_dashboard_test_options;

		if ( ! is_array( $alynt_drime_backups_dashboard_test_options ) ) {
			$alynt_drime_backups_dashboard_test_options = array();
		}

		return array_key_exists( $option, $alynt_drime_backups_dashboard_test_options ) ? $alynt_drime_backups_dashboard_test_options[ $option ] : $default;
	}
}

if ( ! function_exists( 'update_option' ) ) {
	/**
	 * Test update_option shim.
	 *
	 * @param string $option Option.
	 * @param mixed  $value Value.
	 * @param mixed  $autoload Autoload.
	 * @return bool
	 */
	function update_option( $option, $value, $autoload = null ) {
		global $alynt_drime_backups_dashboard_test_options;
		global $alynt_drime_backups_dashboard_test_autoload;

		$alynt_drime_backups_dashboard_test_options[ $option ]  = $value;
		$alynt_drime_backups_dashboard_test_autoload[ $option ] = $autoload;

		return true;
	}
}

if ( ! function_exists( 'delete_option' ) ) {
	/**
	 * Test delete_option shim.
	 *
	 * @param string $option Option.
	 * @return bool
	 */
	function delete_option( $option ) {
		global $alynt_drime_backups_dashboard_test_options;

		unset( $alynt_drime_backups_dashboard_test_options[ $option ] );

		return true;
	}
}

if ( ! function_exists( 'get_current_user_id' ) ) {
	/**
	 * Test get_current_user_id shim.
	 *
	 * @return int
	 */
	function get_current_user_id() {
		global $alynt_drime_backups_dashboard_test_current_user_id;

		return (int) $alynt_drime_backups_dashboard_test_current_user_id;
	}
}

require_once dirname( __DIR__ ) . '/includes/class-event-log-redactor.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-event-log-storage.php';
require_once dirname( __DIR__ ) . '/includes/class-event-log.php';

/**
 * Tests always-on local operator action history.
 */
class EventLogAuditTest extends TestCase {
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
	 * Audit events are stored even when optional diagnostics logging is disabled.
	 *
	 * @return void
	 */
	public function test_audit_actions_are_always_on_and_not_autoloaded() {
		global $alynt_drime_backups_dashboard_test_autoload;

		$log = new Alynt_Drime_Backups_Dashboard_Event_Log();

		$this->assertFalse( $log->is_enabled() );
		$this->assertTrue(
			$log->audit_action(
				'check_status_now',
				'succeeded',
				array(
					'dashboard_site_id' => 7,
				)
			)
		);

		$events = $log->recent_audit_events();

		$this->assertCount( 1, $events );
		$this->assertSame( 42, $events[0]['actor_id'] );
		$this->assertSame( 'check_status_now', $events[0]['action'] );
		$this->assertSame( 'succeeded', $events[0]['outcome'] );
		$this->assertSame( 7, $events[0]['context']['dashboard_site_id'] );
		$this->assertFalse( $alynt_drime_backups_dashboard_test_autoload[ Alynt_Drime_Backups_Dashboard_Event_Log::OPTION_AUDIT ] );
		$this->assertSame( array(), $log->recent_events() );
	}

	/**
	 * Audit context is redacted before local persistence.
	 *
	 * @return void
	 */
	public function test_audit_context_is_redacted_before_storage() {
		$log = new Alynt_Drime_Backups_Dashboard_Event_Log();

		$log->audit_action(
			'create_pending_site',
			'succeeded',
			array(
				'dashboard_site_id' => 3,
				'pairing_token'     => 'adb1.secret-token',
				'authorization'     => 'Bearer secret',
				'expected_origin'   => 'https://client.example.com',
			)
		);

		$events  = $log->recent_audit_events();
		$encoded = wp_json_encode( $events );

		$this->assertSame( '[redacted]', $events[0]['context']['pairing_token'] );
		$this->assertSame( '[redacted]', $events[0]['context']['authorization'] );
		$this->assertStringNotContainsString( 'adb1.secret-token', $encoded );
		$this->assertStringNotContainsString( 'Bearer secret', $encoded );
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
