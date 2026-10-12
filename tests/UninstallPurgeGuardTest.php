<?php
/**
 * Regression coverage for explicit uninstall purge guard.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

/**
 * Tests that permanent uninstall purging remains explicitly gated.
 */
class Alynt_Drime_Backups_Dashboard_Uninstall_Purge_Guard_Test extends TestCase {
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
