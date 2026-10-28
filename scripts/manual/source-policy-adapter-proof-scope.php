<?php
/**
 * Pure finite peer-statement admission, not a general SQL parser or executor.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 1 );
}
/** Source-owned fixed statements and one exact owned table only.
 *
 * @param mixed $sql Fixed peer statement.
 * @param mixed $table Owned clone identifier.
 * @return bool
 */
function adba_sql_scope( $sql, $table ) {
	if ( ! is_string( $sql ) || strlen( $sql ) > 33000 || ! is_string( $table ) || ! preg_match( '/\Aadbb_[a-f0-9]{12}_options\z/', $table ) ) {
		return false;
	}
	if ( in_array( $sql, array( 'START TRANSACTION', 'ROLLBACK', 'SET SESSION max_statement_time=3, innodb_lock_wait_timeout=1, lock_wait_timeout=1', "DELETE FROM $table", "SELECT option_value,autoload FROM $table", "SELECT option_id FROM $table FOR UPDATE" ), true ) ) {
		return true;
	}
	return 1 === preg_match( '/\AINSERT INTO ' . preg_quote( $table, '/' ) . " \(option_name,option_value,autoload\) VALUES \('alynt_drime_backups_dashboard_source_policies',X'([a-f0-9]+)','off'\)\z/", $sql, $matches )
		&& strlen( $matches[1] ) <= 32768 && 0 === strlen( $matches[1] ) % 2;
}
