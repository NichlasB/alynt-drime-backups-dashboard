<?php
/**
 * WordPress shims for admin Sites-list tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

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
