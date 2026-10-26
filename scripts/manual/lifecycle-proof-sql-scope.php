<?php
/**
 * Fixed lifecycle-proof SQL manifest assertion, not a general SQL firewall.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Rejects generated statements targeting anything outside owned fixture tables.
 *
 * @param string $sql Generated statement.
 * @param array  $tables Exact owned table manifest.
 * @return void
 * @throws RuntimeException For an unexpected statement or identifier.
 */
function adb_lifecycle_assert_sql_scope( $sql, array $tables ) {
	if ( ! preg_match( '/^\s*(?:SELECT|SHOW|DESCRIBE|CREATE|ALTER|DROP|INSERT|UPDATE|DELETE)\b/i', $sql ) ) {
		throw new RuntimeException( 'Unexpected lifecycle statement kind' );
	}
	$identifiers = array();
	if ( preg_match_all( '/\b(?:FROM|JOIN|INTO|(?:CREATE|ALTER|DROP)\s+TABLE(?:\s+IF\s+(?:NOT\s+)?EXISTS)?|DESCRIBE)\s+`?([A-Za-z0-9_]+)`?/i', $sql, $matches ) ) {
		$identifiers = $matches[1];
	}
	if ( preg_match( '/^\s*UPDATE\s+`?([A-Za-z0-9_]+)`?/i', $sql, $match ) ) {
		$identifiers[] = $match[1];
	}
	foreach ( $identifiers as $table ) {
		if ( ! in_array( $table, $tables, true ) ) {
			throw new RuntimeException( 'Non-owned lifecycle SQL identifier rejected' );
		}
	}
}
