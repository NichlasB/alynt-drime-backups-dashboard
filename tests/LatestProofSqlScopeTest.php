<?php
/** Socket-free manifest checks, not physical query or privilege proof. */
use PHPUnit\Framework\TestCase;
require_once dirname( __DIR__ ) . '/scripts/manual/latest-proof-sql-scope.php';

class LatestProofSqlScopeTest extends TestCase {
	public function test_fixed_owned_queries_and_schema_clones_are_admitted() {
		$prefix = 'adbls_0123456789ab_';
		$table = $prefix . 'alynt_drime_dashboard_snapshots';
		foreach ( array( 'SELECT DATABASE() AS db, @@port AS port, VERSION() AS version', "SHOW TABLE STATUS WHERE Name = '$table'", 'SHOW FULL COLUMNS FROM `' . $table . '`', 'CREATE TABLE ' . $table . ' LIKE wp_alynt_drime_dashboard_snapshots', 'DROP TABLE ' . $table, 'SELECT * FROM ' . $table . ' WHERE dashboard_site_id=1 ORDER BY id DESC LIMIT 1', 'SELECT s.* FROM ' . $table . ' s JOIN (SELECT MAX(id) latest_id FROM ' . $table . ' GROUP BY dashboard_site_id) x ON x.latest_id=s.id', "INSERT INTO `$table` (payload_json) VALUES ('FROM wp_users;')" ) as $sql ) {
			adb_latest_assert_sql_scope( $sql, $prefix );
			$this->addToAssertionCount( 1 );
		}
	}
	public function test_application_targets_and_unapproved_statement_kinds_are_rejected() {
		$prefix = 'adbls_0123456789ab_';
		$table = $prefix . 'alynt_drime_dashboard_sites';
		foreach ( array( 'SELECT * FROM wp_options', 'SELECT * FROM ' . $prefix . 'options', 'SELECT * FROM ' . $prefix . 'alynt_drime_dashboard_actions', "SELECT * FROM $table JOIN wp_users ON 1=1", "SELECT * FROM $table,wp_options WHERE 1=1", 'SHOW FULL COLUMNS FROM `wp_users`', "DROP TABLE $table,wp_options", "CREATE TABLE $table LIKE wp_users", "DELETE FROM $table", "UPDATE $table SET id=1", "TRUNCATE TABLE $table", "INSERT INTO $table (id) VALUES (1); DROP TABLE wp_users", "SELECT * FROM $table UNION SELECT * FROM wp_users", "SELECT SLEEP(10) FROM $table", 'SET GLOBAL max_statement_time=0', 'GRANT ALL ON local.* TO root' ) as $sql ) {
			$rejected = false;
			try { adb_latest_assert_sql_scope( $sql, $prefix ); } catch ( RuntimeException $error ) { $rejected = true; }
			$this->assertTrue( $rejected, $sql );
		}
		$this->expectException( RuntimeException::class );
		adb_latest_assert_sql_scope( 'SELECT * FROM wp_alynt_drime_dashboard_sites', 'wp_' );
	}
}
