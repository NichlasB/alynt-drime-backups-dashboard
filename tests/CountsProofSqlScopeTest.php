<?php
/** Isolated manifest checks, not physical SQL, authentication or privilege proof. */
use PHPUnit\Framework\TestCase;
require_once dirname( __DIR__ ) . '/scripts/manual/counts-proof-sql-scope.php';
require_once __DIR__ . '/support/count-batch-wpdb-double.php';

class CountsProofSqlScopeTest extends TestCase {
	public function test_actual_grouped_and_scalar_templates_fit_the_owned_manifest() {
		$previous = $GLOBALS['wpdb'] ?? null;
		$db = new Alynt_Drime_Backups_Dashboard_Test_Count_Batch_WPDB();
		$db->prefix = 'adbc_0123456789ab_';
		$GLOBALS['wpdb'] = $db;
		try {
			$snapshots = new Alynt_Drime_Backups_Dashboard_Snapshot_Repository();
			$actions = new Alynt_Drime_Backups_Dashboard_Remote_Action_Repository();
			foreach ( array( 1, 50, 51, 100 ) as $size ) {
				$db->responses = array_fill( 0, 6, array() );
				$snapshots->counts_by_site_ids( range( 1, $size ) );
				$actions->counts_by_site_ids( range( 1, $size ) );
				$actions->counts_by_site_ids( range( 1, $size ), true );
			}
			$snapshots->count_for_site( -2 ); $snapshots->count_for_site( 0 );
			$actions->count_for_site( 1 ); $actions->count_non_terminal_for_site( 1 );
			foreach ( $db->prepared as $statement ) {
				adb_counts_assert_sql_scope( vsprintf( $statement['sql'], $statement['args'] ), $db->prefix );
				$this->addToAssertionCount( 1 );
			}
		} finally { $GLOBALS['wpdb'] = $previous; }
	}

	public function test_fixed_metadata_clones_inserts_and_exact_cleanup_are_admitted() {
		$prefix = 'adbc_0123456789ab_';
		adb_counts_assert_sql_scope( 'SELECT DATABASE() AS db, @@port AS port, VERSION() AS version', $prefix );
		$this->addToAssertionCount( 1 );
		foreach ( array( 'snapshots', 'actions' ) as $kind ) {
			$table = $prefix . 'alynt_drime_dashboard_' . $kind;
			foreach ( array( "SHOW TABLE STATUS WHERE Name = '$table'", "SHOW FULL COLUMNS FROM `$table`", 'SHOW FULL COLUMNS FROM ' . $table, 'CREATE TABLE ' . $table . ' LIKE wp_alynt_drime_dashboard_' . $kind, 'SELECT COUNT(*) FROM ' . $table, 'DROP TABLE ' . $table, "INSERT INTO `$table` (`id`, `payload_json`) VALUES ('1', 'FROM wp_users;')" ) as $sql ) {
				adb_counts_assert_sql_scope( $sql, $prefix );
				$this->addToAssertionCount( 1 );
			}
		}
	}

	public function test_application_unbounded_mixed_and_unapproved_operations_are_rejected() {
		$prefix = 'adbc_0123456789ab_';
		$table = $prefix . 'alynt_drime_dashboard_actions';
		$base = "SELECT dashboard_site_id, COUNT(*) AS row_count FROM $table WHERE dashboard_site_id IN (1) GROUP BY dashboard_site_id";
		foreach ( array( 'SELECT * FROM wp_options', 'SELECT * FROM ' . $prefix . 'options', "SELECT * FROM $table JOIN wp_users ON 1=1", "DROP TABLE $table,wp_users", "CREATE TABLE $table LIKE wp_users", "DELETE FROM $table", "UPDATE $table SET state='running'", "TRUNCATE TABLE $table", "INSERT INTO `$table` (`id`) SELECT id FROM wp_users", "INSERT INTO `$table` (`id`) VALUES (1); DROP TABLE wp_users", "$base UNION SELECT * FROM wp_users", str_replace( 'IN (1)', 'IN (0)', $base ), str_replace( 'IN (1)', 'IN (-1)', $base ), str_replace( 'IN (1)', 'IN (9001)', $base ), str_replace( 'IN (1)', 'IN (' . implode( ',', range( 1, 51 ) ) . ')', $base ), str_replace( ' GROUP BY dashboard_site_id', '', $base ), str_replace( ' GROUP BY', " AND state IN ('running') GROUP BY", $base ), "SELECT SLEEP(1) FROM $table", 'SET GLOBAL max_statement_time=0', 'GRANT ALL ON local.* TO root' ) as $sql ) {
			$rejected = false;
			try { adb_counts_assert_sql_scope( $sql, $prefix ); } catch ( RuntimeException $error ) { $rejected = true; }
			$this->assertTrue( $rejected, $sql );
		}
		$this->expectException( RuntimeException::class );
		adb_counts_assert_sql_scope( $base, 'wp_' );
	}
}
