<?php
/**
 * Fixed generated-statement manifest, not a general SQL firewall/privilege boundary.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Rejects SQL outside the two owned fixture targets and closed statement kinds.
 *
 * @param string $sql Generated statement, never operator SQL.
 * @param string $prefix Owned namespace.
 * @return void
 * @throws RuntimeException For unsupported generated statements.
 */
function adb_latest_assert_sql_scope( $sql, $prefix ) {
	if ( ! preg_match( '/^adbls_[a-f0-9]{12}_$/D', $prefix ) ) {
		throw new RuntimeException( 'Invalid latest-selection fixture namespace' );
	}
	if ( 'SELECT DATABASE() AS db, @@port AS port, VERSION() AS version' === $sql ) {
		return;
	}
	$tables = array( $prefix . 'alynt_drime_dashboard_sites', $prefix . 'alynt_drime_dashboard_snapshots' );
	foreach ( $tables as $index => $table ) {
		$source = array( 'wp_alynt_drime_dashboard_sites', 'wp_alynt_drime_dashboard_snapshots' )[ $index ];
		if ( in_array( $sql, array( "SHOW TABLE STATUS WHERE Name = '$table'", 'SHOW FULL COLUMNS FROM `' . $table . '`', 'SHOW FULL COLUMNS FROM ' . $table, 'CREATE TABLE ' . $table . ' LIKE ' . $source, 'DROP TABLE ' . $table ), true ) ) {
			return;
		}
	}
	$shape = preg_replace( "/'(?:\\\\.|''|[^'\\\\])*'/s", "''", $sql );
	if ( null === $shape || preg_match( '~;|/\*|\*/|--|#|\b(?:UNION|OUTFILE|DUMPFILE|PROCEDURE|SLEEP|BENCHMARK)\b~i', $shape ) || ! preg_match( '/^(?:SELECT\b|INSERT INTO\b)/i', $shape ) ) {
		throw new RuntimeException( 'Unsupported latest-selection statement' );
	}
	if ( ! preg_match_all( '/\b(?:FROM|JOIN|INTO)\s+`?([A-Za-z0-9_.]+)`?/i', $shape, $matches ) ) {
		throw new RuntimeException( 'No owned latest-selection query target' );
	}
	foreach ( $matches[1] as $table ) {
		if ( ! in_array( $table, $tables, true ) ) {
			throw new RuntimeException( 'Non-owned latest-selection target rejected' );
		}
	}
	if ( preg_match( '/`?\s*,\s*`?[A-Za-z0-9_]+\s+(?:WHERE|SET)\b/i', $shape ) ) {
		throw new RuntimeException( 'Unsupported generated multi-target query' );
	}
}
