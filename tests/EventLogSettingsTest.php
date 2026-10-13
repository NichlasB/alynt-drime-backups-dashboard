<?php
/**
 * Event log settings tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/event-log-test-harness.php';

/**
 * Tests the structured diagnostics event log settings.
 */
class EventLogSettingsTest extends TestCase {
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
}
