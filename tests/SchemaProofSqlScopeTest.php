<?php
/** Isolated tests of the manual proof's fixed-SQL scope assertion. */
use PHPUnit\Framework\TestCase;
require_once dirname( __DIR__ ) . '/scripts/manual/schema-proof-sql-scope.php';

class SchemaProofSqlScopeTest extends TestCase {
	public function test_core_option_upsert_does_not_treat_column_update_as_a_table() {
		$table = 'adbpf_0123456789ab_options';
		$sql = "INSERT INTO `$table` (`option_name`, `option_value`, `autoload`) VALUES ('schema', '7', 'off') ON DUPLICATE KEY UPDATE `option_name` = VALUES(`option_name`), `option_value` = VALUES(`option_value`), `autoload` = VALUES(`autoload`)";
		adb_schema_assert_sql_scope( $sql, array( $table ) );
		$this->addToAssertionCount( 1 );
	}
	public function test_owned_read_and_ddl_targets_are_accepted() {
		foreach ( array( 'SELECT * FROM owned', 'CREATE TABLE owned (id int)', 'ALTER TABLE owned ADD id int', 'DROP TABLE owned', 'UPDATE owned SET id=1', 'DESCRIBE owned', 'SELECT * FROM owned JOIN other_owned ON 1=1' ) as $sql ) {
			adb_schema_assert_sql_scope( $sql, array( 'owned', 'other_owned' ) );
			$this->addToAssertionCount( 1 );
		}
	}
	public function test_non_owned_targets_and_statement_kinds_are_rejected() {
		foreach ( array( 'SELECT * FROM wp_options', 'SELECT * FROM other', 'UPDATE other SET id=1', 'DROP TABLE other', 'SELECT * FROM owned JOIN other ON 1=1', 'GRANT ALL ON owned TO user' ) as $sql ) {
			$rejected = false;
			try { adb_schema_assert_sql_scope( $sql, array( 'owned' ) ); } catch ( RuntimeException $error ) { $rejected = true; }
			$this->assertTrue( $rejected, $sql );
		}
	}
}
