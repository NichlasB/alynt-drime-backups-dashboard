<?php
/** Socket-free tests of the manual proof's generated-SQL manifest only. */
use PHPUnit\Framework\TestCase;
require_once dirname( __DIR__ ) . '/scripts/manual/poll-proof-sql-scope.php';

class PollProofSqlScopeTest extends TestCase {
	public function test_fixed_owned_operations_and_schema_only_clone_are_admitted() {
		$table = 'adbrp_0123456789ab_alynt_drime_dashboard_sites';
		foreach ( array( 'SELECT * FROM ' . $table, 'SHOW FULL COLUMNS FROM ' . $table, "SHOW TABLE STATUS WHERE Name = '$table'", 'CREATE TABLE ' . $table . ' LIKE wp_alynt_drime_dashboard_sites', 'UPDATE ' . $table . ' SET overall_status=1', 'INSERT INTO `' . $table . '` (id) VALUES (7)', 'DELETE FROM ' . $table . ' WHERE id=7', 'DROP TABLE ' . $table, 'START TRANSACTION', 'COMMIT', 'ROLLBACK', 'SELECT CONNECTION_ID()' ) as $sql ) {
			adb_poll_assert_sql_scope( $sql, $table );
			$this->addToAssertionCount( 1 );
		}
	}
	public function test_application_mixed_targets_and_unapproved_statement_kinds_are_rejected() {
		$table = 'adbrp_0123456789ab_alynt_drime_dashboard_sites';
		foreach ( array( 'UPDATE wp_alynt_drime_dashboard_sites SET enrollment_status=1', 'SELECT * FROM wp_options', 'SELECT * FROM ' . $table . ' JOIN wp_users ON 1=1', 'CREATE TABLE other LIKE wp_alynt_drime_dashboard_sites', 'CREATE TABLE ' . $table . ' LIKE wp_options', 'DROP TABLE wp_options', 'GRANT ALL ON ' . $table . ' TO root', 'TRUNCATE TABLE ' . $table, 'SET GLOBAL max_statement_time=0', 'KILL 123', 'SELECT * FROM information_schema.PROCESSLIST' ) as $sql ) {
			$rejected = false;
			try { adb_poll_assert_sql_scope( $sql, $table ); } catch ( RuntimeException $error ) { $rejected = true; }
			$this->assertTrue( $rejected, $sql );
		}
	}
	public function test_multi_target_mutations_are_not_in_the_owned_manifest() {
		$table = 'adbrp_0123456789ab_alynt_drime_dashboard_sites';
		foreach ( array( 'DROP TABLE ' . $table . ',wp_options', 'UPDATE ' . $table . ',wp_options SET id=1' ) as $sql ) {
			$rejected = false;
			try { adb_poll_assert_sql_scope( $sql, $table ); } catch ( RuntimeException $error ) { $rejected = true; }
			$this->assertTrue( $rejected, $sql );
		}
	}
}
