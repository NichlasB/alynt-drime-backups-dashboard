<?php
/**
 * Event log tests.
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

require_once dirname( __DIR__ ) . '/includes/class-event-log.php';

/**
 * Tests the structured diagnostics event log.
 */
class EventLogTest extends TestCase {
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
	 * Logging is disabled by default.
	 *
	 * @return void
	 */
	public function test_logging_is_disabled_by_default() {
		$log = new Alynt_Drime_Backups_Dashboard_Event_Log();

		$this->assertFalse( $log->is_enabled() );
		$this->assertFalse( $log->log( 'error', 'rest', 'pairing_invalid', 'Pairing failed.' ) );
		$this->assertSame( array(), $log->recent_events() );
	}

	/**
	 * Settings are sanitized and stored with autoload disabled.
	 *
	 * @return void
	 */
	public function test_settings_are_sanitized_and_not_autoloaded() {
		global $alynt_drime_backups_dashboard_test_autoload;

		$log = new Alynt_Drime_Backups_Dashboard_Event_Log();
		$log->update_settings(
			array(
				'enabled'        => '1',
				'minimum_level'  => 'error',
				'retention_days' => 999,
				'max_events'     => 1,
			)
		);

		$settings = $log->settings();

		$this->assertTrue( $settings['enabled'] );
		$this->assertSame( 'error', $settings['minimum_level'] );
		$this->assertSame( 90, $settings['retention_days'] );
		$this->assertSame( 10, $settings['max_events'] );
		$this->assertFalse( $alynt_drime_backups_dashboard_test_autoload[ Alynt_Drime_Backups_Dashboard_Event_Log::OPTION_SETTINGS ] );
	}

	/**
	 * Events below threshold are skipped and sensitive context is redacted.
	 *
	 * @return void
	 */
	public function test_threshold_and_redaction_are_applied_before_storage() {
		$log = new Alynt_Drime_Backups_Dashboard_Event_Log();
		$log->update_settings(
			array(
				'enabled'       => true,
				'minimum_level' => 'warning',
			)
		);

		$this->assertFalse( $log->log( 'info', 'rest', 'ignored', 'Info event.' ) );
		$this->assertTrue(
			$log->log(
				'error',
				'rest',
				'pairing_invalid',
				'Pairing failed.',
				array(
					'dashboard_site_id' => 7,
					'authorization'     => 'Bearer secret',
					'raw_payload'       => '{"secret":"value"}',
					'nested'            => array(
						'polling_secret' => 'abc',
						'status'         => 'failed',
					),
				)
			)
		);

		$events  = $log->recent_events();
		$encoded = wp_json_encode( $events );

		$this->assertCount( 1, $events );
		$this->assertSame( 'pairing_invalid', $events[0]['code'] );
		$this->assertSame( '[redacted]', $events[0]['context']['authorization'] );
		$this->assertSame( '[redacted]', $events[0]['context']['raw_payload'] );
		$this->assertSame( '[redacted]', $events[0]['context']['nested']['polling_secret'] );
		$this->assertStringNotContainsString( 'Bearer secret', $encoded );
		$this->assertStringNotContainsString( '{"secret":"value"}', $encoded );
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
}
