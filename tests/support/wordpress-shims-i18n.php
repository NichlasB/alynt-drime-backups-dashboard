<?php
/**
 * Minimal i18n WordPress shims for pure unit tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! function_exists( '__' ) ) {
	/**
	 * Minimal translation shim for pure unit tests.
	 *
	 * @param string $text Text.
	 * @param string $domain Domain.
	 * @return string
	 */
	function __( $text, $domain = 'default' ) {
		unset( $domain );
		return $text;
	}
}

if ( ! function_exists( '_n' ) ) {
	/**
	 * Minimal plural translation shim for pure unit tests.
	 *
	 * @param string $single Single text.
	 * @param string $plural Plural text.
	 * @param int    $number Number.
	 * @param string $domain Domain.
	 * @return string
	 */
	function _n( $single, $plural, $number, $domain = 'default' ) {
		unset( $domain );
		return 1 === (int) $number ? $single : $plural;
	}
}
