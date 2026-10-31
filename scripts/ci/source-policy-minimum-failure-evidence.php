<?php
/**
 * Bounded private diagnostic DATA only; no native calls or admission grants.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/** Encodes the already captured first failure without messages, paths or payloads.
 *
 * @param array      $failure First failure snapshot.
 * @param array|null $inventory Candidate-reported inventory, not attestation.
 * @return string Fixed ASCII record, or empty on malformed DATA.
 */
function adbm_failure_wire( $failure, $inventory ) {
	$stages = array( 'source-audit', 'ambient', 'escape', 'positive', 'noop-stale', 'update', 'race', 'duplicate', 'transform-reentry', 'lock', 'post-change', 'errno' );
	$phases = array( 'runtime', 'core-pins', 'core-load', 'owned-load', 'cache', 'profile', 'image', 'native' );
	$types  = array( 'RuntimeException', 'Error', 'TypeError', 'ValueError', 'ParseError', 'ArgumentCountError', 'ReflectionException', 'other' );
	if ( ! is_array( $failure ) || ! in_array( $failure['stage'] ?? null, $stages, true ) || ! in_array( $failure['phase'] ?? null, $phases, true )
		|| ! is_int( $failure['checks'] ?? null ) || $failure['checks'] < 0 || $failure['checks'] > 99999
		|| ! is_int( $failure['groups'] ?? null ) || $failure['groups'] < 0 || $failure['groups'] > 9
		|| ! is_string( $failure['assertion'] ?? null ) || ! preg_match( '/\A[a-f0-9]{64}\z/', $failure['assertion'] )
		|| ! in_array( $failure['exception'] ?? null, $types, true ) ) {
		return '';
	}
	$severity = 0;
	$source   = 'none';
	$line     = 0;
	if ( null !== ( $failure['diagnostic'] ?? null ) ) {
		$diag = $failure['diagnostic'];
		if ( ! is_array( $diag ) || ! in_array( $diag['severity'] ?? null, array( 1, 2, 4, 8, 16, 32, 64, 128, 256, 512, 1024, 2048, 4096, 8192, 16384 ), true )
			|| ! in_array( $diag['source'] ?? null, array( 'worker', 'core', 'other' ), true ) || ! is_int( $diag['line'] ?? null ) || $diag['line'] < 1 || $diag['line'] > 999999 ) {
			return '';
		}
		$severity = $diag['severity'];
		$source   = $diag['source'];
		$line     = $diag['line'];
	}
	return 'ADBMF2|' . $failure['stage'] . '|' . $failure['phase'] . '|' . $failure['checks'] . '|' . $failure['groups'] . '|'
		. $failure['assertion'] . '|' . $failure['exception'] . '|' . $severity . '|' . $source . '|' . $line . '|' . adbm_inventory_wire( $inventory ) . "\n";
}
/** Bounds inventory independently so malformed inventory cannot hide first cause.
 *
 * @param array|null $inventory Candidate-reported DATA.
 * @return string Fixed facts and bounded names, absent or invalid marker.
 */
function adbm_inventory_wire( $inventory ) {
	if ( null === $inventory ) {
		return 'uuuuuuu|-';
	}
	if ( ! is_array( $inventory ) || ! is_string( $inventory['facts'] ?? null ) || ! preg_match( '/\A[01u]{7}\z/', $inventory['facts'] )
		|| ! is_array( $inventory['names'] ?? null ) || count( $inventory['names'] ) < 1 || count( $inventory['names'] ) > 64 ) {
		return 'uuuuuuu|!';
	}
	foreach ( $inventory['names'] as $name ) {
		if ( ! is_string( $name ) || ! preg_match( '/\A[A-Za-z0-9_ ]{1,64}\z/', $name ) ) {
			return 'uuuuuuu|!';
		}
	}
	$sorted = array_values( array_unique( $inventory['names'] ) );
	sort( $sorted, SORT_STRING );
	return $sorted === $inventory['names'] ? $inventory['facts'] . '|' . implode( ';', $sorted ) : 'uuuuuuu|!';
}
