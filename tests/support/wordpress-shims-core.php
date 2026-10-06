<?php
/**
 * Minimal core WordPress shims for pure unit tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! function_exists( 'trailingslashit' ) ) {
	/**
	 * Minimal trailingslashit shim.
	 *
	 * @param mixed $value Value.
	 * @return string
	 */
	function trailingslashit( $value ) {
		return rtrim( (string) $value, '/\\' ) . DIRECTORY_SEPARATOR;
	}
}

if ( ! function_exists( 'plugin_dir_path' ) ) {
	/**
	 * Minimal plugin_dir_path shim.
	 *
	 * @param string $file File path.
	 * @return string
	 */
	function plugin_dir_path( $file ) {
		return trailingslashit( dirname( $file ) );
	}
}

if ( ! function_exists( 'plugin_dir_url' ) ) {
	/**
	 * Minimal plugin_dir_url shim.
	 *
	 * @param string $file File path.
	 * @return string
	 */
	function plugin_dir_url( $file ) {
		return 'https://example.org/wp-content/plugins/' . basename( dirname( $file ) ) . '/';
	}
}

if ( ! function_exists( 'plugin_basename' ) ) {
	/**
	 * Minimal plugin_basename shim.
	 *
	 * @param string $file File path.
	 * @return string
	 */
	function plugin_basename( $file ) {
		return basename( dirname( $file ) ) . '/' . basename( $file );
	}
}

if ( ! function_exists( 'register_activation_hook' ) ) {
	/**
	 * Minimal register_activation_hook shim.
	 *
	 * @param string   $file     Plugin file.
	 * @param callable $callback Activation callback.
	 * @return void
	 */
	function register_activation_hook( $file, $callback ) {
		unset( $file, $callback );
	}
}

if ( ! function_exists( 'register_deactivation_hook' ) ) {
	/**
	 * Minimal register_deactivation_hook shim.
	 *
	 * @param string   $file     Plugin file.
	 * @param callable $callback Deactivation callback.
	 * @return void
	 */
	function register_deactivation_hook( $file, $callback ) {
		unset( $file, $callback );
	}
}

if ( ! function_exists( 'add_action' ) ) {
	/**
	 * Minimal add_action shim.
	 *
	 * @param string   $hook          Hook name.
	 * @param callable $callback      Hook callback.
	 * @param int      $priority      Priority.
	 * @param int      $accepted_args Accepted arguments.
	 * @return void
	 */
	function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		unset( $hook, $callback, $priority, $accepted_args );
	}
}

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
