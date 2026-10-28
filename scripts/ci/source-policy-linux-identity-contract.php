<?php
/**
 * Pure identity-observation consistency, never a native-profile admission grant.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/** Contains no connection, filesystem, WordPress or application operations. */
final class Alynt_Drime_Backups_Dashboard_Linux_Identity_Contract {
	/**
	 * Checks fixed read-only audit DATA, including unqualified build suffixes.
	 *
	 * @param mixed $row Observed one-row metadata.
	 * @param mixed $wire Version observed independently through mysqli.
	 * @param mixed $transport Observed client transport description.
	 * @return bool Consistency only, not supported-version policy.
	 */
	public static function valid( $row, $wire, $transport ) {
		$keys = array( 'version', 'comment', 'machine', 'os', 'no_tcp', 'read_only', 'ac', 'tx', 'socket', 'connection_id', 'application_schemas', 'other_sessions', 'transactions', 'principal' );
		if ( ! is_array( $row ) || count( $row ) !== count( $keys ) || array_diff( $keys, array_keys( $row ) )
			|| ! is_string( $wire ) || '' === $wire || strlen( $wire ) > 128 || 'Localhost via UNIX socket' !== $transport ) {
			return false;
		}
		foreach ( $row as $value ) {
			if ( ! is_string( $value ) || strlen( $value ) > 512 || preg_match( '/[^\x20-\x7e]/', $value ) ) {
				return false;
			}
		}
		return $row['version'] === $wire && 'x86_64' === $row['machine'] && '' !== $row['os'] && '' !== $row['comment']
			&& '1' === $row['no_tcp'] && '1' === $row['read_only'] && '1' === $row['ac'] && '0' === $row['tx']
			&& '/run/adb-owned/native.sock' === $row['socket'] && 'root@localhost' === $row['principal']
			&& '0' === $row['application_schemas'] && '0' === $row['other_sessions'] && '0' === $row['transactions']
			&& 1 === preg_match( '/\A[1-9][0-9]{0,19}\z/', $row['connection_id'] );
	}
}
