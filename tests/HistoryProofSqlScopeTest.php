<?php
/** Isolated generated-statement manifest checks; not SQL/privilege proof. */
use PHPUnit\Framework\TestCase;
require_once dirname( __DIR__ ) . '/scripts/manual/history-proof-sql-scope.php';
require_once __DIR__ . '/support/snapshot-history-batch-wpdb-double.php';

class HistoryProofSqlScopeTest extends TestCase {
	public function test_actual_repository_templates_fit_the_fixed_owned_manifest() {
		$previous = $GLOBALS['wpdb'] ?? null;
		$db = new Alynt_Drime_Backups_Dashboard_Test_History_Batch_WPDB();
		$db->prefix = 'adbh_0123456789ab_';
		$GLOBALS['wpdb'] = $db;
		try {
			$repository = new Alynt_Drime_Backups_Dashboard_Snapshot_Repository();
			foreach ( array( 1, 2, 50, 51, 100 ) as $size ) {
				$repository->recent_by_site_ids( range( 1, $size ) );
			}
			foreach ( $db->prepared as $statement ) {
				adb_history_assert_sql_scope( vsprintf( $statement['sql'], $statement['args'] ), $db->prefix );
				$this->addToAssertionCount( 1 );
			}
		} finally { $GLOBALS['wpdb'] = $previous; }
	}

	public function test_owned_metadata_setup_and_cleanup_are_admitted() {
		$prefix = 'adbh_0123456789ab_';
		$table = $prefix . 'alynt_drime_dashboard_snapshots';
		foreach ( array( 'SELECT DATABASE() AS db, @@port AS port, VERSION() AS version', "SHOW TABLE STATUS WHERE Name = '$table'", "SHOW FULL COLUMNS FROM `$table`", 'SHOW FULL COLUMNS FROM ' . $table, 'CREATE TABLE ' . $table . ' LIKE wp_alynt_drime_dashboard_snapshots', 'SELECT COUNT(*) FROM ' . $table, 'DROP TABLE ' . $table, "INSERT INTO `$table` (`id`, `payload_json`) VALUES ('1', 'FROM wp_users;')" ) as $sql ) {
			adb_history_assert_sql_scope( $sql, $prefix );
			$this->addToAssertionCount( 1 );
		}
	}

	public function test_application_mixed_unbounded_or_unapproved_operations_are_rejected() {
		$prefix = 'adbh_0123456789ab_';
		$table = $prefix . 'alynt_drime_dashboard_snapshots';
		$columns = 'id, dashboard_site_id, schema_version, observed_at, overall_status, queue_count, uploaded_count, failed_count, active_upload, warning_count, cron_status';
		$branch = "SELECT $columns FROM $table WHERE dashboard_site_id = 1 ORDER BY observed_at DESC, id DESC LIMIT 10";
		foreach ( array( 'SELECT * FROM wp_options', 'SELECT * FROM ' . $prefix . 'options', "SELECT * FROM $table JOIN wp_users ON 1=1", "DROP TABLE $table,wp_users", "CREATE TABLE $table LIKE wp_users", "DELETE FROM $table", "UPDATE $table SET id=1", "TRUNCATE TABLE $table", "INSERT INTO `$table` (`id`) SELECT id FROM wp_users", "INSERT INTO `$table` (`id`) VALUES (1); DROP TABLE wp_users", "($branch) UNION ALL (SELECT * FROM wp_users)", str_replace( 'LIMIT 10', 'LIMIT 0', $branch ), str_replace( 'LIMIT 10', 'LIMIT 51', $branch ), str_replace( ' = 1 ', ' = 9001 ', $branch ), str_replace( 'LIMIT 10', '', $branch ), "($branch) UNION ALL ($branch)", '(' . implode( ') UNION ALL (', array_fill( 0, 51, $branch ) ) . ') ORDER BY dashboard_site_id ASC, observed_at DESC, id DESC', "SELECT SLEEP(10) FROM $table", 'SET GLOBAL max_statement_time=0', 'GRANT ALL ON local.* TO root' ) as $sql ) {
			$rejected = false;
			try { adb_history_assert_sql_scope( $sql, $prefix ); } catch ( RuntimeException $error ) { $rejected = true; }
			$this->assertTrue( $rejected, $sql );
		}
		$this->expectException( RuntimeException::class );
		adb_history_assert_sql_scope( $branch, 'wp_' );
	}
}
