<?php
/**
 * Manual proof SQL assertion.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Checks this fixed proof's generated SQL, not an arbitrary SQL firewall.
 *
 * @param string $sql Generated statement.
 * @param array  $tables Exact owned identifier manifest.
 * @return void
 * @throws RuntimeException When a generated statement leaves the manifest.
 */
function adb_schema_assert_sql_scope( $sql, array $tables ) {
	if ( preg_match( '/\bwp_[A-Za-z0-9_]+\b/', $sql ) ) {
		throw new RuntimeException( 'Application SQL rejected' );
	}
	if ( ! preg_match( '/^\s*(?:SELECT|SHOW|DESCRIBE|CREATE|ALTER|DROP|INSERT|UPDATE)\b/i', $sql ) ) {
		throw new RuntimeException( 'Unexpected statement kind' );
	}
	$identifiers = array();
	if ( preg_match_all( '/\b(?:FROM|JOIN|INTO|TABLE|DESCRIBE)\s+`?([A-Za-z0-9_]+)`?/i', $sql, $matches ) ) {
		$identifiers = $matches[1];
	}
	// Only a leading UPDATE introduces a table; an upsert UPDATE introduces columns.
	if ( preg_match( '/^\s*UPDATE\s+`?([A-Za-z0-9_]+)`?/i', $sql, $match ) ) {
		$identifiers[] = $match[1];
	}
	foreach ( $identifiers as $table ) {
		if ( ! in_array( $table, $tables, true ) ) {
			throw new RuntimeException( 'Non-owned SQL identifier rejected' );
		}
	}
}
