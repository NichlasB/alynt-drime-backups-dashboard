<?php
/**
 * Regression coverage for safe dashboard uninstall behavior.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/uninstall-safety-test-harness.php';

/**
 * Tests that uninstall cannot erase persistent dashboard data by accident.
 */
class Alynt_Drime_Backups_Dashboard_Uninstall_Safety_Test extends TestCase {
	/**
	 * Resets lifecycle capture state before each test.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		Alynt_Drime_Backups_Dashboard_Uninstall_Safety_Recorder::reset();

		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			define( 'WP_UNINSTALL_PLUGIN', true );
		}
	}

	/**
	 * Canonical uninstalls preserve persistent monitoring data by default.
	 *
	 * @return void
	 */
	public function test_canonical_uninstall_preserves_dashboard_tables_and_options_by_default() {
		global $wpdb;
		$wpdb = new Alynt_Drime_Backups_Dashboard_Uninstall_Safety_Wpdb();

		include dirname( __DIR__ ) . '/uninstall.php';

		$this->assertSame(
			array(
				'alynt_drime_backups_dashboard_poll_sites',
				'alynt_drime_backups_dashboard_cleanup_snapshots',
			),
			Alynt_Drime_Backups_Dashboard_Uninstall_Safety_Recorder::$cleared_hooks
		);
		$this->assertSame( array(), Alynt_Drime_Backups_Dashboard_Uninstall_Safety_Recorder::$deleted_options );

		foreach ( $wpdb->queries as $query ) {
			$this->assertStringNotContainsString( 'DROP TABLE', $query );
		}
	}

}
