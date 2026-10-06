<?php
/**
 * Event log test harness.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

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
		global $alynt_drime_backups_dashboard_test_unchanged_update_fails;

		if (
			! empty( $alynt_drime_backups_dashboard_test_unchanged_update_fails )
			&& array_key_exists( $option, $alynt_drime_backups_dashboard_test_options )
			&& $alynt_drime_backups_dashboard_test_options[ $option ] === $value
		) {
			return false;
		}

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

require_once dirname( __DIR__, 2 ) . '/includes/class-event-log-redactor.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-event-log-storage.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-event-log-settings.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-event-log-reporting.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-event-log.php';
