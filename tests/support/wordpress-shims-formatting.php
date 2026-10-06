<?php
/**
 * Minimal formatting WordPress shims for pure unit tests.
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

if ( ! function_exists( 'absint' ) ) {
	/**
	 * Minimal absint shim.
	 *
	 * @param mixed $value Value.
	 * @return int
	 */
	function absint( $value ) {
		return abs( (int) $value );
	}
}

if ( ! function_exists( 'sanitize_key' ) ) {
	/**
	 * Minimal sanitize_key shim.
	 *
	 * @param mixed $value Value.
	 * @return string
	 */
	function sanitize_key( $value ) {
		return strtolower( preg_replace( '/[^a-z0-9_\-]/i', '', (string) $value ) );
	}
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	/**
	 * Minimal sanitize_text_field shim.
	 *
	 * @param mixed $value Value.
	 * @return string
	 */
	function sanitize_text_field( $value ) {
		return trim( (string) $value );
	}
}

if ( ! function_exists( 'esc_url_raw' ) ) {
	/**
	 * Minimal esc_url_raw shim.
	 *
	 * @param mixed $value Value.
	 * @return string
	 */
	function esc_url_raw( $value ) {
		return trim( (string) $value );
	}
}

if ( ! function_exists( 'wp_unslash' ) ) {
	/**
	 * Minimal wp_unslash shim.
	 *
	 * @param mixed $value Value.
	 * @return mixed
	 */
	function wp_unslash( $value ) {
		return $value;
	}
}

if ( ! function_exists( 'wp_json_encode' ) ) {
	/**
	 * Minimal wp_json_encode shim.
	 *
	 * @param mixed $value Value to encode.
	 * @param int   $flags Flags.
	 * @param int   $depth Depth.
	 * @return string|false
	 */
	function wp_json_encode( $value, $flags = 0, $depth = 512 ) {
		return json_encode( $value, $flags, $depth );
	}
}
