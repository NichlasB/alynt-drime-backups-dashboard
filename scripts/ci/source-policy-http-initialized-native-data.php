<?php
/**
 * Fixed native query-hook registry metadata; no invocation or DB handle return.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Only the source-owned observer and genuine pinned wpdb placeholder restorer.
 * wpdb may still be constructing before assignment to the global at query time.
 *
 * @param mixed  $callbacks Actual WP_Hook callback map.
 * @param string $core_file Fixed manifest-bound class-wpdb.php path.
 * @return bool
 */
function adbh_initialized_query_registry( $callbacks, $core_file ) {
	if ( ! is_array( $callbacks ) || ! isset( $callbacks[1] ) || ! is_array( $callbacks[1] ) || 1 !== count( $callbacks[1] ) || count( $callbacks ) > 2 ) {
		return false;
	}
	foreach ( $callbacks as $priority => $entries ) {
		if ( ! in_array( $priority, array( 0, 1 ), true ) || ! is_array( $entries ) || 1 !== count( $entries ) ) {
			return false;
		}
		$entry = reset( $entries );
		if ( ! is_array( $entry ) || array( 'function', 'accepted_args' ) !== array_keys( $entry ) || 1 !== $entry['accepted_args'] ) {
			return false;
		}
		if ( 1 === $priority ) {
			if ( 'adbh_initializer_query' !== $entry['function'] ) {
				return false;
			}
		} else {
			$fn = $entry['function'];
			if ( ! is_array( $fn ) || array( 0, 1 ) !== array_keys( $fn ) || ! is_object( $fn[0] ) || 'wpdb' !== get_class( $fn[0] ) || 'remove_placeholder_escape' !== $fn[1] || ! method_exists( $fn[0], $fn[1] ) || ( new ReflectionMethod( $fn[0], $fn[1] ) )->getFileName() !== $core_file ) {
				return false;
			}
		}
	}
	return true;
}
