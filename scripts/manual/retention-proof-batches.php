<?php
/**
 * Actual retention SQL, deterministic boundary rows and bounded batch cases.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared -- Exact approved owned fixture manifest only.
/**
 * Seeds a bounded large fixture using prepared values in small INSERT batches.
 *
 * @param string $kind Fixed fixture kind.
 * @param int    $count Bounded row count.
 * @return void
 */
function adb_retention_bulk( $kind, $count ) {
	global $wpdb, $tables;
	adb_retention_check( in_array( $kind, array( 'snapshots', 'actions' ), true ) && $count > 0 && $count <= 5002, 'bulk fixture cap' );
	$rows = array();
	for ( $id = 1; $id <= $count; ++$id ) {
		$rows[] = 'snapshots' === $kind
			? $wpdb->prepare( '(%d,1,%s,%s)', $id, '2000-01-01 00:00:00', '{"owned":true}' )
			: $wpdb->prepare( '(%d,%s,1,%s,%s,%s,%s,%s,%s)', $id, 'owned-' . $id, '2000-01-01 00:00:00', '2000-01-01 00:00:00', '2099-01-01 00:00:00', '{}', '2000-01-01 00:00:00', '2000-01-01 00:00:00' );
		if ( 1000 === count( $rows ) || $id === $count ) {
			$columns = 'snapshots' === $kind ? '(id,dashboard_site_id,observed_at,payload_json)' : '(id,public_id,dashboard_site_id,requested_at,completed_at,expires_at,redacted_context_json,created_at,updated_at)';
			adb_retention_query( 'INSERT INTO ' . $tables[ 'snapshots' === $kind ? 1 : 2 ] . ' ' . $columns . ' VALUES ' . implode( ',', $rows ) );
			$rows = array();
		}
	}
}

/**
 * Runs real prepared SQL cases; no alternate cleanup implementation or virtual clock.
 *
 * @return void
 */
function adb_retention_batch_cases() {
	global $wpdb, $tables, $groups, $before_delete, $deletes;
	$snapshots = new Alynt_Drime_Backups_Dashboard_Snapshot_Repository();
	$actions   = new Alynt_Drime_Backups_Dashboard_Remote_Action_Repository();
	adb_retention_reset();
	foreach ( array( array( 1, 1, '2000-01-01 00:00:00' ), array( 2, 1, '2001-01-01 00:00:00' ), array( 3, 1, '1999-01-01 00:00:00' ), array( 4, 2, '2000-01-01 00:00:00' ), array( 5, 3, '2099-01-01 00:00:00' ) ) as $row ) {
		adb_retention_insert( 'snapshots', $row[0], $row[1], $row[2] );
	}
	adb_retention_check( 1 === $snapshots->cleanup_retention( 30, 1 ), 'one-row snapshot batch succeeds' );
	adb_retention_check( array( 2, 3, 4, 5 ) === adb_retention_ids( 1 ), 'oldest eligible ID first, backdated highest ID protected' );
	adb_retention_check( 1 === $snapshots->cleanup_retention(), 'remaining old non-latest snapshot removed' );
	adb_retention_check( array( 3, 4, 5 ) === adb_retention_ids( 1 ), 'latest per site including sole ancient snapshot preserved' );
	adb_retention_check( 0 === $snapshots->cleanup_retention(), 'repeat snapshot no-op is integer zero' );
	adb_retention_check( 3 === (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $tables[1] . ' WHERE payload_json = \'{"owned":true}\'' ), 'retained snapshot payloads unchanged' );
	$groups[] = 'snapshot age, batch order and per-site latest preservation';
	adb_retention_reset();
	foreach ( array( array( 1, '2000-01-01 00:00:00' ), array( 2, '2000-01-01 00:00:00' ), array( 3, null ), array( 4, '2099-01-01 00:00:00' ) ) as $row ) {
		adb_retention_insert( 'actions', $row[0], 1, $row[1] );
	}
	adb_retention_check( 1 === $actions->cleanup_retention( 90, 1 ), 'one-row action batch succeeds' );
	adb_retention_check( array( 2, 3, 4 ) === adb_retention_ids( 2 ), 'action tie orders by ID, not all old requested rows' );
	adb_retention_check( 1 === $actions->cleanup_retention(), 'remaining old completed action removed' );
	adb_retention_check( array( 3, 4 ) === adb_retention_ids( 2 ), 'incomplete and recent actions preserved' );
	adb_retention_check( 0 === $actions->cleanup_retention(), 'repeat action no-op is integer zero' );
	$groups[] = 'completed-action age, stable order and incomplete preservation';
	adb_retention_reset();
	// The observer seeds exact real prepared-cutoff values before the original DELETE executes.
	$before_delete     = function ( $kind, $sql ) {
		global $before_delete;
		$column = 'snapshots' === $kind ? 'observed_at' : 'completed_at';
		adb_retention_check( 1 === preg_match( '/' . $column . " < '([0-9 :-]+)'/", $sql, $match ), 'real prepared cutoff observed' );
		$before_delete = null;
		adb_retention_insert( $kind, 1, 1, $match[1] );
		adb_retention_insert( $kind, 2, 1, gmdate( 'Y-m-d H:i:s', strtotime( $match[1] . ' UTC' ) - 1 ) );
		if ( 'snapshots' === $kind ) {
			adb_retention_insert( $kind, 3, 1, '2099-01-01 00:00:00' );
		}
	};
	$boundary_observer = $before_delete;
	adb_retention_check( 1 === $snapshots->cleanup_retention( 0, 0 ), 'snapshot lower clamps and strict boundary delete exactly earlier row' );
	adb_retention_check( array( 1, 3 ) === adb_retention_ids( 1 ), 'exact snapshot cutoff retained physically' );
	$before_delete = $boundary_observer;
	adb_retention_check( 1 === $actions->cleanup_retention( 0, 0 ), 'action lower clamps and strict boundary delete exactly earlier row' );
	adb_retention_check( array( 1 ) === adb_retention_ids( 2 ), 'exact action cutoff retained physically' );
	foreach ( $deletes as $delete ) {
		adb_retention_check( 1 === preg_match( "/< '([0-9 :-]+)'/", $delete[1], $match ), 'lower cutoff parsed' );
		adb_retention_check( abs( time() - strtotime( $match[1] . ' UTC' ) - 86400 ) <= 2 && 1 === preg_match( '/LIMIT 1$/', trim( $delete[1] ) ), 'lower retention/day and batch clamp observed in actual SQL' );
	}
	$groups[] = 'strict physical cutoff and lower argument clamps';
	adb_retention_reset();
	foreach ( array(
		'snapshots' => $snapshots,
		'actions'   => $actions,
	) as $kind => $repository ) {
		adb_retention_bulk( $kind, 5002 );
		if ( 'snapshots' === $kind ) {
			adb_retention_insert( $kind, 6000, 1, '2099-01-01 00:00:00' );
		}
		adb_retention_check( 5000 === $repository->cleanup_retention( 9999, 9999 ), 'upper batch cap physically limits deletion to5000' );
		adb_retention_check( ( 'snapshots' === $kind ? array( 5001, 5002, 6000 ) : array( 5001, 5002 ) ) === adb_retention_ids( 'snapshots' === $kind ? 1 : 2 ), 'upper cap preserves the exact remainder' );
		$last = end( $deletes );
		adb_retention_check( 1 === preg_match( "/< '([0-9 :-]+)'/", $last[1], $match ) && abs( time() - strtotime( $match[1] . ' UTC' ) - 365 * 86400 ) <= 2, 'upper retention cap observed in actual prepared SQL' );
	}
	$groups[] = 'physical5000-row maximum and365-day upper retention clamp';
}
