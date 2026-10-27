<?php
/**
 * Generated-query manifest for the owned history proof, not a SQL firewall.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Rejects statements outside the one owned fixture and finite query shapes.
 *
 * @param string $sql Generated statement.
 * @param string $prefix Exact owned namespace.
 * @return void
 * @throws RuntimeException For unsupported generated SQL.
 */
function adb_history_assert_sql_scope( $sql, $prefix ) {
	if ( ! preg_match( '/^adbh_[a-f0-9]{12}_$/D', $prefix ) ) {
		throw new RuntimeException( 'Invalid owned history namespace' );
	}
	$table = $prefix . 'alynt_drime_dashboard_snapshots';
	if ( in_array( $sql, array( 'SELECT DATABASE() AS db, @@port AS port, VERSION() AS version', "SHOW TABLE STATUS WHERE Name = '$table'", 'SHOW FULL COLUMNS FROM `' . $table . '`', 'SHOW FULL COLUMNS FROM ' . $table, 'CREATE TABLE ' . $table . ' LIKE wp_alynt_drime_dashboard_snapshots', 'SELECT COUNT(*) FROM ' . $table, 'DROP TABLE ' . $table ), true ) ) {
		return;
	}
	$shape = preg_replace( "/'(?:\\\\.|''|[^'\\\\])*'/s", "''", $sql );
	if ( null === $shape || preg_match( '~;|/\*|\*/|--|#|\b(?:OUTFILE|DUMPFILE|PROCEDURE|SLEEP|BENCHMARK|JOIN)\b~i', $shape ) ) {
		throw new RuntimeException( 'Unsupported owned history statement' );
	}
	$target = preg_quote( $table, '/' );
	if ( preg_match( '/^INSERT INTO `' . $target . '` \((?:`[a-z_]+`(?:, )?)+\) VALUES \((?:[0-9]+|\x27\x27)(?:, (?:[0-9]+|\x27\x27))*\)$/D', $shape ) ) {
		return;
	}
	$normalized = preg_replace( '/\s+/', ' ', trim( $sql ) );
	$columns    = 'id, dashboard_site_id, schema_version, observed_at, overall_status, queue_count, uploaded_count, failed_count, active_upload, warning_count, cron_status';
	$branch     = '/^SELECT ' . $columns . ' FROM ' . $target . ' WHERE dashboard_site_id = ([0-9]+) ORDER BY observed_at DESC, id DESC LIMIT ([0-9]+)$/D';
	$selects    = array( $normalized );
	if ( '(' === substr( $normalized, 0, 1 ) ) {
		$suffix = ') ORDER BY dashboard_site_id ASC, observed_at DESC, id DESC';
		if ( substr( $normalized, -strlen( $suffix ) ) !== $suffix ) {
			throw new RuntimeException( 'Unbounded or unordered history union' );
		}
		$selects = explode( ') UNION ALL (', substr( $normalized, 1, -strlen( $suffix ) ) );
		if ( count( $selects ) < 2 || count( $selects ) > 50 ) {
			throw new RuntimeException( 'Owned history branch budget rejected' );
		}
	}
	foreach ( $selects as $select ) {
		if ( ! preg_match( $branch, $select, $values ) || (int) $values[1] < 1 || (int) $values[1] > 9000 || (int) $values[2] < 1 || (int) $values[2] > 50 ) {
			throw new RuntimeException( 'Owned history branch rejected' );
		}
	}
}
