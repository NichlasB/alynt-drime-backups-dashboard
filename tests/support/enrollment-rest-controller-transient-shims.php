<?php
/**
 * Enrollment REST controller transient shims.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! function_exists( 'get_transient' ) ) {
	/**
	 * Test transient getter.
	 *
	 * @param string $key Transient key.
	 * @return mixed
	 */
	function get_transient( $key ) {
		return isset( $GLOBALS['alynt_drime_backups_dashboard_test_transients'][ $key ] )
			? $GLOBALS['alynt_drime_backups_dashboard_test_transients'][ $key ]
			: false;
	}
}

if ( ! function_exists( 'set_transient' ) ) {
	/**
	 * Test transient setter.
	 *
	 * @param string $key Transient key.
	 * @param mixed  $value Value.
	 * @param int    $expiration Expiration.
	 * @return bool
	 */
	function set_transient( $key, $value, $expiration = 0 ) {
		unset( $expiration );
		$GLOBALS['alynt_drime_backups_dashboard_test_transients'][ $key ] = $value;

		return true;
	}
}

if ( ! function_exists( 'delete_transient' ) ) {
	/**
	 * Test transient deleter.
	 *
	 * @param string $key Transient key.
	 * @return bool
	 */
	function delete_transient( $key ) {
		unset( $GLOBALS['alynt_drime_backups_dashboard_test_transients'][ $key ] );

		return true;
	}
}
