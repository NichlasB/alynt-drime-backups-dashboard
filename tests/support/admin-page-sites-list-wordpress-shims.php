<?php
/**
 * WordPress shims for admin Sites-list tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/wordpress-shims-admin.php';
require_once __DIR__ . '/wordpress-shims-escaping.php';
require_once __DIR__ . '/wordpress-shims-sanitization.php';

if ( ! function_exists( 'wp_list_pluck' ) ) {
	/**
	 * Minimal wp_list_pluck() test double.
	 *
	 * @param array<int,array<string,mixed>> $list List.
	 * @param string                         $field Field name.
	 * @return array<int,mixed>
	 */
	function wp_list_pluck( $list, $field ) {
		return array_map(
			static function ( $item ) use ( $field ) {
				return isset( $item[ $field ] ) ? $item[ $field ] : null;
			},
			$list
		);
	}
}

if ( ! function_exists( 'esc_html__' ) ) {
	/**
	 * Minimal esc_html__() test double.
	 *
	 * @param string $text Text.
	 * @param string $domain Domain.
	 * @return string
	 */
	function esc_html__( $text, $domain = 'default' ) {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( '__' ) ) {
	/**
	 * Minimal __() test double.
	 *
	 * @param string $text Text.
	 * @param string $domain Domain.
	 * @return string
	 */
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	/**
	 * Minimal esc_html() test double.
	 *
	 * @param string $text Text.
	 * @return string
	 */
	function esc_html( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	/**
	 * Minimal esc_attr() test double.
	 *
	 * @param string $text Text.
	 * @return string
	 */
	function esc_attr( $text ) {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_attr__' ) ) {
	/**
	 * Minimal esc_attr__() test double.
	 *
	 * @param string $text Text.
	 * @param string $domain Domain.
	 * @return string
	 */
	function esc_attr__( $text, $domain = 'default' ) {
		return esc_attr( $text );
	}
}

if ( ! function_exists( 'number_format_i18n' ) ) {
	/**
	 * Minimal number_format_i18n() test double.
	 *
	 * @param int|float $number Number.
	 * @param int       $decimals Decimals.
	 * @return string
	 */
	function number_format_i18n( $number, $decimals = 0 ) {
		return number_format( (float) $number, (int) $decimals );
	}
}
