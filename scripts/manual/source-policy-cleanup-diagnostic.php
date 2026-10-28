<?php
/**
 * Rejected evidence diagnostics only; never native admission or success receipts.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once __DIR__ . '/source-policy-cleanup-evidence.php';

/** Fixed numeric/enum projection; no SQL, messages, credentials or handles. */
final class Alynt_Drime_Backups_Dashboard_Cleanup_Diagnostic {
	/**
	 * Formats one correlated rejected row without changing its acceptance.
	 *
	 * @param mixed $row Original terminal observations.
	 * @param mixed $run Expected run correlation; not exported.
	 * @param mixed $case_name Expected fixed case.
	 * @param mixed $source Expected image correlation; not exported.
	 * @param mixed $pid Exact owned-process correlation.
	 * @return string Bounded diagnostic or fixed unsafe marker, never an ACK.
	 */
	public static function format( $row, $run, $case_name, $source, $pid ) {
		$unsafe = "ADBC-DIAG-UNSAFE\n";
		if ( ! is_array( $row ) || array_keys( $row ) !== Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::fields()
			|| ! is_string( $run ) || 1 !== preg_match( '/\A[a-f0-9]{32}\z/', $run ) || ! is_string( $source ) || 1 !== preg_match( '/\A[a-f0-9]{64}\z/', $source )
			|| ! in_array( $case_name, Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::cases(), true ) || ! is_int( $pid ) || $pid < 1 || $pid > 4294967295 ) {
			return $unsafe;
		}
		foreach ( $row as $value ) {
			if ( ! is_string( $value ) || strlen( $value ) > 256 ) {
				return $unsafe;
			}
		}
		if ( 'ADBC1' !== $row['version'] || $run !== $row['run'] || $source !== $row['source'] || $case_name !== $row['case'] || (string) $pid !== $row['pid'] || Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::PROFILE !== $row['profile'] ) {
			return $unsafe;
		}
		foreach ( array( 'before', 'bootstrap', 'after' ) as $key ) {
			if ( 1 !== preg_match( '/\A(?:0|[1-9][0-9]{0,19})(?:,(?:0|[1-9][0-9]{0,19})){10}\z/', $row[ $key ] ) ) {
				return $unsafe;
			}
		}
		if ( ! in_array( $row['outcome'], array( 'active', 'unavailable' ), true ) || ! in_array( $row['state'], array( 'never_opened', 'allocating', 'allocated', 'connecting', 'connected_unqualified', 'active', 'closed', 'closure_unknown', 'preflight' ), true ) ) {
			return $unsafe;
		}
		$terminal = array();
		foreach ( array_slice( Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::fields(), 9 ) as $key ) {
			if ( ! in_array( $key, array( 'outcome', 'state' ), true ) && 1 !== preg_match( '/\A(?:0|[1-9][0-9]{0,4})\z/', $row[ $key ] ) ) {
				return $unsafe;
			}
			$terminal[] = $row[ $key ];
		}
		$zero = implode( ',', array_fill( 0, 11, '0' ) );
		$wire = implode( '|', array( 'ADBC-DIAG1', $case_name, (string) $pid, $zero === $row['before'] ? '1' : '0', $row['bootstrap'], $row['after'], implode( ',', $terminal ) ) ) . "\n";
		return strlen( $wire ) <= 256 ? $wire : $unsafe;
	}
}
