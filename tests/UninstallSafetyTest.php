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
	 * Recorded cleared hooks.
	 *
	 * @var string[]
	 */
	public static $cleared_hooks = array();

	/**
	 * Recorded deleted transients.
	 *
	 * @var string[]
	 */
	public static $deleted_transients = array();

	/**
	 * Recorded deleted options.
	 *
	 * @var string[]
	 */
	public static $deleted_options = array();

	/**
	 * Resets lifecycle capture state before each test.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		self::$cleared_hooks      = array();
		self::$deleted_transients = array();
		self::$deleted_options    = array();

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

			$this->assertSame( array(), self::$cleared_hooks );
			$this->assertSame( array(), self::$deleted_transients );
			$this->assertSame( array(), self::$deleted_options );
			$this->assertSame( array(), $wpdb->queries );
		} finally {
			unlink( $copy_path );
			rmdir( $temporary_directory );
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
			self::$cleared_hooks
		);
		$this->assertSame( array(), self::$deleted_options );

		foreach ( $wpdb->queries as $query ) {
			$this->assertStringNotContainsString( 'DROP TABLE', $query );
		}
	}

	/**
	 * Permanent data removal remains an intentional deployment-time decision.
	 *
	 * @return void
	 */
	public function test_permanent_purge_requires_the_explicit_wp_config_constant() {
		$uninstall_source = file_get_contents( dirname( __DIR__ ) . '/uninstall.php' );

		$this->assertIsString( $uninstall_source );
		$this->assertStringContainsString( "defined( 'ALYNT_DRIME_BACKUPS_DASHBOARD_PURGE_DATA_ON_UNINSTALL' )", $uninstall_source );
		$this->assertStringContainsString( 'true !== ALYNT_DRIME_BACKUPS_DASHBOARD_PURGE_DATA_ON_UNINSTALL', $uninstall_source );
		$this->assertStringContainsString( "alynt_drime_dashboard_actions", $uninstall_source );
		$this->assertStringContainsString( "delete_option( 'alynt_drime_backups_dashboard_source_policies' )", $uninstall_source );
	}
}
