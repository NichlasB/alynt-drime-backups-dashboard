<?php
/**
 * Fixed generated-SQL manifest for the manually approved poll proof, not a firewall.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Rejects statement targets outside one owned fixture and fixed read-only metadata.
 *
 * @param string $sql Generated SQL.
 * @param string $table Exact fixture table.
 * @return void
 * @throws RuntimeException For unsupported SQL or a non-owned target.
 */
function adb_poll_assert_sql_scope( $sql, $table ) {
	if ( ! preg_match( '/^adbrp_[a-f0-9]{12}_alynt_drime_dashboard_sites$/D', $table ) ) {
		throw new RuntimeException( 'Invalid owned table manifest' );
	}
	$fixed = array(
		'SELECT CONNECTION_ID()',
		'SELECT @@in_transaction',
		'START TRANSACTION',
		'COMMIT',
		'ROLLBACK',
		"SHOW TABLE STATUS WHERE Name = '$table'",
		'CREATE TABLE ' . $table . ' LIKE wp_alynt_drime_dashboard_sites',
		'DROP TABLE ' . $table,
	);
	if ( in_array( $sql, $fixed, true ) ) {
		return;
	}
	if ( preg_match( '/^SELECT STATE FROM information_schema\.PROCESSLIST WHERE ID=[1-9][0-9]* AND DB=DATABASE\(\)$/D', $sql ) ) {
		return;
	}
	if ( preg_match( '/^SELECT trx_mysql_thread_id,trx_state,trx_id,trx_rows_locked FROM information_schema\.INNODB_TRX WHERE trx_mysql_thread_id IN \(CONNECTION_ID\(\),[1-9][0-9]*\)$/D', $sql ) ) {
		return;
	}
	if ( preg_match( '/^SELECT COUNT\(\*\) FROM information_schema\.INNODB_LOCK_WAITS w JOIN information_schema\.INNODB_TRX r ON r\.trx_id=w\.requesting_trx_id JOIN information_schema\.INNODB_TRX b ON b\.trx_id=w\.blocking_trx_id WHERE r\.trx_mysql_thread_id=[1-9][0-9]* AND b\.trx_mysql_thread_id=CONNECTION_ID\(\)$/D', $sql ) ) {
		return;
	}
	if ( ! preg_match( '/^\s*(?:SELECT|SHOW FULL COLUMNS|INSERT|UPDATE|DELETE)\b/i', $sql ) ) {
		throw new RuntimeException( 'Unsupported poll-proof statement kind' );
	}
	if ( preg_match( '/^\s*UPDATE\b/i', $sql ) && ! preg_match( '/^UPDATE `?' . preg_quote( $table, '/' ) . '`? SET /', $sql ) ) {
		throw new RuntimeException( 'Only a single fixed UPDATE target is supported' );
	}
	$targets = array();
	if ( preg_match_all( '/\b(?:FROM|JOIN|INTO|DROP\s+TABLE)\s+`?([A-Za-z0-9_.]+)`?/i', $sql, $match ) ) {
		$targets = $match[1];
	}
	if ( preg_match( '/^\s*UPDATE\s+`?([A-Za-z0-9_.]+)`?/i', $sql, $match ) ) {
		$targets[] = $match[1];
	}
	if ( ! $targets ) {
		throw new RuntimeException( 'No supported fixture target' );
	}
	foreach ( $targets as $target ) {
		if ( $table !== $target ) {
			throw new RuntimeException( 'Non-owned poll-proof target rejected' );
		}
	}
}
