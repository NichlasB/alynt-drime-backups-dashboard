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
		return $thing instanceof WP_Error;
	}
}
