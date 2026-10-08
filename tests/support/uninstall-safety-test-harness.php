<?php
/**
 * Uninstall safety test harness.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/uninstall-safety-wpdb-double.php';

if ( ! function_exists( 'wp_clear_scheduled_hook' ) ) {
	/**
	 * Records an unscheduled hook for lifecycle tests.
	 *
	 * @param string $hook Hook name.
	 * @return void
	 */
	function wp_clear_scheduled_hook( $hook ) {
		Alynt_Drime_Backups_Dashboard_Uninstall_Safety_Test::$cleared_hooks[] = $hook;
	}
}

if ( ! function_exists( 'delete_transient' ) ) {
	/**
	 * Records a deleted transient for lifecycle tests.
	 *
	 * @param string $transient Transient name.
	 * @return bool
	 */
	function delete_transient( $transient ) {
		Alynt_Drime_Backups_Dashboard_Uninstall_Safety_Test::$deleted_transients[] = $transient;
		return true;
	}
}

if ( ! function_exists( 'delete_option' ) ) {
	/**
	 * Records a deleted option for lifecycle tests.
	 *
	 * @param string $option Option name.
	 * @return bool
	 */
	function delete_option( $option ) {
		Alynt_Drime_Backups_Dashboard_Uninstall_Safety_Test::$deleted_options[] = $option;
		return true;
	}
}
