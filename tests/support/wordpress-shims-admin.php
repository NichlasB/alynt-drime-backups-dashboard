<?php
/**
 * Minimal admin/url WordPress shims for pure unit tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! function_exists( 'add_management_page' ) ) {
	/**
	 * Minimal add_management_page shim.
	 *
	 * @param string   $page_title Page title.
	 * @param string   $menu_title Menu title.
	 * @param string   $capability Capability.
	 * @param string   $menu_slug Menu slug.
	 * @param callable $callback Callback.
	 * @return string
	 */
	function add_management_page( $page_title, $menu_title, $capability, $menu_slug, $callback ) {
		unset( $page_title, $menu_title, $capability, $callback );
		return 'tools_page_' . sanitize_key( $menu_slug );
	}
}

if ( ! function_exists( 'admin_url' ) ) {
	/**
	 * Minimal admin_url shim.
	 *
	 * @param string $path Admin path.
	 * @return string
	 */
	function admin_url( $path = '' ) {
		return 'https://example.org/wp-admin/' . ltrim( (string) $path, '/' );
	}
}

if ( ! function_exists( 'add_query_arg' ) ) {
	/**
	 * Minimal add_query_arg shim.
	 *
	 * @param array<string,string> $args Query args.
	 * @param string               $url URL.
	 * @return string
	 */
	function add_query_arg( $args, $url = '' ) {
		$separator = false === strpos( $url, '?' ) ? '?' : '&';
		return $url . $separator . http_build_query( $args, '', '&' );
	}
}

if ( ! function_exists( 'nocache_headers' ) ) {
	/**
	 * Minimal nocache_headers shim.
	 *
	 * @return void
	 */
	function nocache_headers() {
		$GLOBALS['alynt_drime_backups_dashboard_nocache_headers_sent'] = isset( $GLOBALS['alynt_drime_backups_dashboard_nocache_headers_sent'] )
			? (int) $GLOBALS['alynt_drime_backups_dashboard_nocache_headers_sent'] + 1
			: 1;
	}
}
