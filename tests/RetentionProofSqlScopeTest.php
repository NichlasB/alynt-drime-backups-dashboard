<?php
/** Socket-free manifest checks, not database privilege or physical proof. */
use PHPUnit\Framework\TestCase;
require_once dirname( __DIR__ ) . '/scripts/manual/retention-proof-sql-scope.php';

class RetentionProofSqlScopeTest extends TestCase {
	public function test_owned_queries_and_schema_only_clones_have_positive_controls() {
		$prefix = 'adbrt_0123456789ab_'; $snapshots = $prefix . 'alynt_drime_dashboard_snapshots'; $options = $prefix . 'options';
		foreach ( array( 'SHOW FULL COLUMNS FROM `' . $snapshots . '`', 'SELECT * FROM ' . $snapshots, 'DELETE FROM ' . $snapshots . ' WHERE id NOT IN (SELECT latest_id FROM (SELECT MAX(id) AS latest_id FROM ' . $snapshots . ' GROUP BY dashboard_site_id) latest_snapshots) LIMIT 1', "UPDATE $options SET option_value='a:1:{s:4:\"FROM\";s:10:\"wp_options\";}' WHERE option_name='owned'", "INSERT INTO `$options` (option_name,option_value) VALUES ('owned','FROM wp_users;')", 'CREATE TABLE ' . $snapshots . ' LIKE wp_alynt_drime_dashboard_snapshots', 'DROP TABLE ' . $snapshots, "SHOW TABLE STATUS WHERE Name = '$options'" ) as $sql ) {
			adb_retention_assert_sql_scope( $sql, $prefix ); $this->addToAssertionCount( 1 );
		}
	}
	public function test_application_mixed_targets_and_statement_escalation_are_rejected() {
		$prefix = 'adbrt_0123456789ab_'; $table = $prefix . 'options';
		foreach ( array( 'SHOW FULL COLUMNS FROM `wp_users`', 'SELECT * FROM wp_options', 'DELETE FROM wp_alynt_drime_dashboard_snapshots', "SELECT * FROM $table JOIN wp_users ON 1=1", "UPDATE $table,wp_options SET option_id=1", "DROP TABLE $table,wp_options", "CREATE TABLE $table LIKE wp_users", "DELETE FROM $table; DELETE FROM wp_options", "DELETE FROM $table /* scoped */", 'SET GLOBAL max_statement_time=0', 'GRANT ALL ON local.* TO root', 'TRUNCATE TABLE ' . $table, 'SELECT 1' ) as $sql ) {
			$rejected = false; try { adb_retention_assert_sql_scope( $sql, $prefix ); } catch ( RuntimeException $error ) { $rejected = true; }
			$this->assertTrue( $rejected, $sql );
		}
		$this->expectException( RuntimeException::class ); adb_retention_assert_sql_scope( 'SELECT * FROM wp_options', 'wp_' );
	}
}
