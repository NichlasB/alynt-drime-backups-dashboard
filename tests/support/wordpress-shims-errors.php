<?php
/**
 * Minimal WordPress error shims for pure unit tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/wordpress-shims-error-class.php';

if ( ! function_exists( 'is_wp_error' ) ) {
	/**
	 * Minimal is_wp_error shim.
	 *
	 * @param mixed $thing Thing.
	 * @return bool
	 */
	function is_wp_error( $thing ) {
		if ( $thing instanceof WP_Error && defined( 'ADB_POLICY_ISOLATED' ) && function_exists( 'do_action' ) ) {
			do_action( 'is_wp_error_instance', $thing );
		}
		return $thing instanceof WP_Error;
	}
}
