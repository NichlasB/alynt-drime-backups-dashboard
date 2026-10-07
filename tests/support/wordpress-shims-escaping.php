<?php
/**
 * Minimal escaping WordPress shims for pure unit tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! function_exists( 'esc_html' ) ) {
	/**
	 * Minimal esc_html shim for pure unit tests.
	 *
	 * @param mixed $text Text.
	 * @return string
	 */
	function esc_html( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	/**
	 * Minimal esc_attr shim for pure unit tests.
	 *
	 * @param mixed $text Text.
	 * @return string
	 */
	function esc_attr( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_url' ) ) {
	/**
	 * Minimal esc_url shim.
	 *
	 * @param mixed $url URL.
	 * @return string
	 */
	function esc_url( $url ) {
		return esc_attr( $url );
	}
}

if ( ! function_exists( 'esc_html__' ) ) {
	/**
	 * Minimal esc_html__ shim for pure unit tests.
	 *
	 * @param string $text Text.
	 * @param string $domain Domain.
	 * @return string
	 */
	function esc_html__( $text, $domain = 'default' ) {
		return esc_html( __( $text, $domain ) );
	}
}

if ( ! function_exists( 'esc_attr__' ) ) {
	/**
	 * Minimal esc_attr__ shim for pure unit tests.
	 *
	 * @param string $text Text.
	 * @param string $domain Domain.
	 * @return string
	 */
	function esc_attr__( $text, $domain = 'default' ) {
		return esc_attr( __( $text, $domain ) );
	}
}
