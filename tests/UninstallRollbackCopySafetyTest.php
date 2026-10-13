<?php
/**
 * Regression coverage for safe dashboard rollback-copy uninstall behavior.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/uninstall-safety-test-harness.php';

/**
 * Tests that rollback-copy uninstall files cannot erase canonical data.
 */
class UninstallRollbackCopySafetyTest extends TestCase {
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
	 * A WordPress-discovered rollback copy must not touch the canonical data.
	 *
	 * @return void
	 */
	public function test_rollback_copy_uninstall_returns_before_any_cleanup() {
		$temporary_directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'alynt-dashboard-rollback-copy-' . uniqid( '', true );
		$this->assertTrue( mkdir( $temporary_directory, 0700, true ) );

		$copy_path = $temporary_directory . DIRECTORY_SEPARATOR . 'uninstall.php';
		$this->assertTrue( copy( dirname( __DIR__ ) . '/uninstall.php', $copy_path ) );

		global $wpdb;
		$wpdb = new Alynt_Drime_Backups_Dashboard_Uninstall_Safety_Wpdb();

		try {
			include $copy_path;

			$this->assertSame( array(), Alynt_Drime_Backups_Dashboard_Uninstall_Safety_Recorder::$cleared_hooks );
			$this->assertSame( array(), Alynt_Drime_Backups_Dashboard_Uninstall_Safety_Recorder::$deleted_transients );
			$this->assertSame( array(), Alynt_Drime_Backups_Dashboard_Uninstall_Safety_Recorder::$deleted_options );
			$this->assertSame( array(), $wpdb->queries );
		} finally {
			unlink( $copy_path );
			rmdir( $temporary_directory );
		}
	}
}
