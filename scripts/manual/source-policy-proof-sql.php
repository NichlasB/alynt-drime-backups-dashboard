<?php
/**
 * Fixed owned-table SQL construction for the manual source-policy proof only.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Builds a byte-sensitive predicate; the identifier can only name a fresh fixture.
 *
 * @param string $table Owned options clone.
 * @return string Prepared-SQL template, never executed here.
 * @throws RuntimeException If the owned identifier is invalid.
 */
function adbp_update_template( $table ) {
	if ( ! is_string( $table ) || ! preg_match( '/\Aadbp_[a-f0-9]{12}_options\z/', $table ) ) {
		throw new RuntimeException( 'Owned option table rejected' );
	}
	return "UPDATE $table SET option_value=%s,autoload=%s WHERE option_name=%s AND BINARY option_name=BINARY %s AND BINARY option_value=BINARY %s AND OCTET_LENGTH(option_value)=%d AND BINARY autoload=BINARY %s AND OCTET_LENGTH(autoload)=%d";
}

/**
 * Checks the generated statement target, not arbitrary SQL or untrusted callbacks.
 *
 * @param string $sql Generated SQL.
 * @param string $table Owned clone.
 * @param bool   $core_add_control Explicit genuine-core reference window, never candidate mode.
 * @return void
 * @throws RuntimeException On any unsupported statement/target.
 */
function adbp_sql_scope( $sql, $table, $core_add_control = false ) {
	adbp_update_template( $table );
	// Only the reference Options API branch may emit this exact core-generated upsert.
	$control_suffix = ' ON DUPLICATE KEY UPDATE `option_name` = VALUES(`option_name`), `option_value` = VALUES(`option_value`), `autoload` = VALUES(`autoload`)';
	if ( true === $core_add_control && 0 === strpos( $sql, "INSERT INTO `$table` (`option_name`, `option_value`, `autoload`) VALUES ('alynt_drime_backups_dashboard_source_policies', " ) && substr( $sql, -strlen( $control_suffix ) ) === $control_suffix ) {
		return;
	}
	if ( in_array( $sql, array( 'ROLLBACK', 'COMMIT', 'START TRANSACTION', 'SELECT CONNECTION_ID()', 'SELECT @@in_transaction', 'SET SESSION max_statement_time=3, innodb_lock_wait_timeout=1, lock_wait_timeout=1', "SHOW TABLE STATUS WHERE Name = '$table'", "CREATE TABLE $table LIKE wp_options", "DROP TABLE $table" ), true ) ) {
		return;
	}
	if ( ! preg_match( '/\A(?:SELECT|INSERT INTO|UPDATE|DELETE FROM|SHOW INDEX FROM|SHOW FULL COLUMNS FROM)\b/', $sql ) || preg_match( '/\b(?:JOIN|UNION|REPLACE|IGNORE|DUPLICATE|TRUNCATE)\b/i', $sql ) || preg_match( '/;\s*(?:SELECT|UPDATE|DELETE|INSERT|DROP|CREATE)\b/i', $sql ) ) {
		throw new RuntimeException( 'Unsupported owned statement' );
	}
	preg_match_all( '/\b(?:FROM|INTO|UPDATE)\s+`?([A-Za-z0-9_.]+)`?/i', $sql, $targets );
	if ( ! $targets[1] || array( $table ) !== array_values( array_unique( $targets[1] ) ) ) {
		throw new RuntimeException( 'Non-owned option target rejected' );
	}
}
