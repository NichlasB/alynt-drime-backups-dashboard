<?php
/**
 * Fixed generated-statement manifest, not a SQL firewall or privilege boundary.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Admits only the two owned fixtures and finite count/setup/cleanup statements.
 *
 * @param string $sql Generated statement.
 * @param string $prefix Owned namespace.
 * @return void
 * @throws RuntimeException For unsupported generated SQL.
 */
function adb_counts_assert_sql_scope( $sql, $prefix ) {
	if ( ! preg_match( '/^adbc_[a-f0-9]{12}_$/D', $prefix ) ) {
		throw new RuntimeException( 'Invalid owned count namespace' );
	}
	if ( 'SELECT DATABASE() AS db, @@port AS port, VERSION() AS version' === $sql ) {
		return;
	}
	$shape = preg_replace( "/'(?:\\\\.|''|[^'\\\\])*'/s", "''", $sql );
	if ( null === $shape || preg_match( '~;|/\*|\*/|--|#|\b(?:UNION|JOIN|OUTFILE|DUMPFILE|PROCEDURE|SLEEP|BENCHMARK)\b~i', $shape ) ) {
		throw new RuntimeException( 'Unsupported owned count statement' );
	}
	$normalized = preg_replace( '/\s+/', ' ', trim( $sql ) );
	foreach ( array( 'snapshots', 'actions' ) as $kind ) {
		$table = $prefix . 'alynt_drime_dashboard_' . $kind;
		if ( in_array( $sql, array( "SHOW TABLE STATUS WHERE Name = '$table'", 'SHOW FULL COLUMNS FROM `' . $table . '`', 'SHOW FULL COLUMNS FROM ' . $table, 'CREATE TABLE ' . $table . ' LIKE wp_alynt_drime_dashboard_' . $kind, 'SELECT COUNT(*) FROM ' . $table, 'DROP TABLE ' . $table ), true ) ) {
			return;
		}
		$target = preg_quote( $table, '/' );
		if ( preg_match( '/^INSERT INTO `' . $target . '` \((?:`[a-z_]+`(?:, )?)+\) VALUES \((?:[0-9]+|\x27\x27)(?:, (?:[0-9]+|\x27\x27))*\)$/D', $shape ) ) {
			return;
		}
		$state  = 'actions' === $kind ? "(?: AND state IN \\('queued_for_dispatch', 'accepted', 'running'\\))?" : '';
		$group  = '/^SELECT dashboard_site_id, COUNT\(\*\) AS row_count FROM ' . $target . ' WHERE dashboard_site_id IN \((-?[0-9]+(?:,-?[0-9]+)*)\)' . $state . ' GROUP BY dashboard_site_id$/D';
		$single = '/^SELECT COUNT\(\*\) FROM ' . $target . ' WHERE dashboard_site_id = (-?[0-9]+)' . $state . '$/D';
		if ( preg_match( $group, $normalized, $values ) || preg_match( $single, $normalized, $values ) ) {
			$ids = array_map( 'intval', explode( ',', $values[1] ) );
			if ( count( $ids ) <= 50 && min( $ids ) >= ( 'actions' === $kind ? 1 : -9000 ) && max( $ids ) <= 9000 ) {
				return;
			}
		}
	}
	throw new RuntimeException( 'Non-owned or unbounded count statement rejected' );
}
