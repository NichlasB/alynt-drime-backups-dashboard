<?php
/**
 * Fixed first-party retention proof SQL manifest, not a general SQL firewall.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Rejects generated statements outside the exact three-table owned manifest.
 *
 * @param string $sql Generated SQL, never caller/request supplied.
 * @param string $prefix Owned attempt prefix.
 * @return void
 * @throws RuntimeException For a non-owned target or unsupported statement.
 */
function adb_retention_assert_sql_scope( $sql, $prefix ) {
	if ( ! preg_match( '/^adbrt_[a-f0-9]{12}_$/D', $prefix ) ) {
		throw new RuntimeException( 'Invalid retention fixture prefix' );
	}
	$tables = array( $prefix . 'options', $prefix . 'alynt_drime_dashboard_snapshots', $prefix . 'alynt_drime_dashboard_actions' );
	foreach ( $tables as $index => $table ) {
		$source = array( 'wp_options', 'wp_alynt_drime_dashboard_snapshots', 'wp_alynt_drime_dashboard_actions' )[ $index ];
		if ( in_array( $sql, array( "SHOW TABLE STATUS WHERE Name = '$table'", 'SHOW FULL COLUMNS FROM ' . $table, 'SHOW FULL COLUMNS FROM `' . $table . '`', 'CREATE TABLE ' . $table . ' LIKE ' . $source, 'DROP TABLE ' . $table ), true ) ) {
			return;
		}
	}
	// Ignore quoted values only for generated-statement lexical inspection, not SQL execution.
	$shape = preg_replace( "/'(?:\\\\.|''|[^'\\\\])*'/s", "''", $sql );
	if ( null === $shape || preg_match( '~;|/\*|\*/|--|#~', $shape ) || ! preg_match( '/^\s*(?:SELECT|INSERT|UPDATE|DELETE)\b/i', $shape ) ) {
		throw new RuntimeException( 'Unsupported retention statement' );
	}
	if ( preg_match( '/^\s*UPDATE\b/i', $shape ) && ! preg_match( '/^UPDATE `?' . preg_quote( $tables[0], '/' ) . '`? SET /', $shape ) ) {
		throw new RuntimeException( 'Only owned option updates are supported' );
	}
	$targets = array();
	if ( preg_match_all( '/\b(?:FROM|JOIN|INTO)\s+`?([A-Za-z0-9_.]+)`?/i', $shape, $matches ) ) {
		$targets = $matches[1];
	}
	if ( preg_match( '/^\s*UPDATE\s+`?([A-Za-z0-9_.]+)`?/i', $shape, $match ) ) {
		$targets[] = $match[1];
	}
	if ( ! $targets ) {
		throw new RuntimeException( 'No supported fixture target' );
	}
	foreach ( $targets as $target ) {
		if ( ! in_array( $target, $tables, true ) ) {
			throw new RuntimeException( 'Non-owned retention SQL target rejected' );
		}
	}
}
