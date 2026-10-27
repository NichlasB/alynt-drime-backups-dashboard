<?php
/** Process-isolated API/IO shims, never genuine authentication/cache evidence. */
if ( defined( 'ADB_POLICY_ISOLATED' ) ) {
if ( class_exists( 'WP_Hook', false ) || function_exists( 'current_user_can' ) || function_exists( 'apply_filters' ) ) {
	throw new RuntimeException( 'Isolated policy environment must not contain genuine or unrelated APIs.' );
}
class WP_Hook { public $callbacks = array(); }
class WP_Object_Cache {}
function wp_using_ext_object_cache() { return $GLOBALS['policy_ext_cache']; }
function current_user_can( $capability ) { $GLOBALS['policy_trace'][] = 'capability'; return 'manage_options' === $capability && $GLOBALS['policy_capable']; }
function wp_verify_nonce( $nonce, $action ) { $GLOBALS['policy_trace'][] = 'nonce'; return 'valid' === $nonce && 'alynt_drime_backups_dashboard_update_source_policy' === $action; }
function apply_filters( $hook, $value, ...$args ) {
	$GLOBALS['policy_trace'][] = $hook;
	foreach ( isset( $GLOBALS['wp_filter'][ $hook ] ) ? $GLOBALS['wp_filter'][ $hook ]->callbacks : array() as $entries ) {
		foreach ( $entries as $entry ) { $value = call_user_func_array( $entry['function'], array_slice( array_merge( array( $value ), $args ), 0, $entry['accepted_args'] ) ); }
	}
	return $value;
}
function do_action( $hook, ...$args ) {
	$GLOBALS['policy_trace'][] = $hook;
	foreach ( isset( $GLOBALS['wp_filter'][ $hook ] ) ? $GLOBALS['wp_filter'][ $hook ]->callbacks : array() as $entries ) {
		foreach ( $entries as $entry ) { call_user_func_array( $entry['function'], array_slice( $args, 0, $entry['accepted_args'] ) ); }
	}
}
function sanitize_option( $name, $value ) { return apply_filters( 'sanitize_option_' . $name, $value, $name, $value ); }
function maybe_serialize( $value ) { return serialize( $value ); }
function wp_cache_delete( $key, $group = '' ) {
	$GLOBALS['policy_trace'][] = 'cache-delete:' . $key;
	if ( $GLOBALS['policy_cache_fail'] ) return false;
	unset( $GLOBALS['policy_cache'][ $group ][ $key ] ); return true;
}
function wp_cache_get( $key, $group = '', $force = false, &$found = null ) {
	$found = isset( $GLOBALS['policy_cache'][ $group ][ $key ] );
	return $found ? $GLOBALS['policy_cache'][ $group ][ $key ] : false;
}
function policy_isolated_hook( $hook, $callback, $accepted = 3 ) {
	if ( ! isset( $GLOBALS['wp_filter'][ $hook ] ) ) $GLOBALS['wp_filter'][ $hook ] = new WP_Hook();
	$GLOBALS['wp_filter'][ $hook ]->callbacks[10][] = array( 'function' => $callback, 'accepted_args' => $accepted );
}
}
