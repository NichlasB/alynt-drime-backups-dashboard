<?php
/** Owned proof SQL construction regressions; no native execution. */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/scripts/manual/source-policy-proof-sql.php';

final class SourcePolicyNativeProofScopeTest extends TestCase {
	public function test_byte_predicate_binds_value_and_autoload_without_upsert() {
		$sql = adbp_update_template( 'adbp_123456abcdef_options' );
		$this->assertStringContainsString( 'WHERE option_name=%s AND BINARY option_name=BINARY %s AND BINARY option_value=BINARY %s AND OCTET_LENGTH(option_value)=%d', $sql );
		$this->assertStringContainsString( 'AND BINARY autoload=BINARY %s AND OCTET_LENGTH(autoload)=%d', $sql );
		$this->assertStringNotContainsString( 'DUPLICATE', $sql );
		adbp_sql_scope( str_replace( array( '%s', '%d' ), array( "'owned'", '5' ), $sql ), 'adbp_123456abcdef_options' );
	}

	/** @dataProvider rejected_targets */
	public function test_only_exact_owned_table_identifiers_are_admitted( $table ) {
		$this->expectException( RuntimeException::class );
		adbp_update_template( $table );
	}

	public static function rejected_targets() {
		return array_map( function ( $name ) { return array( $name ); }, array( 'wp_options', 'adbp_123456abcdef_options extra', 'local.adbp_123456abcdef_options', 'adbp_123456ABCDEF_options', 'adbp_123456abcdef_options;', '' ) );
	}

	/** @dataProvider rejected_statements */
	public function test_scope_rejects_non_owned_or_unsupported_generated_sql( $sql ) {
		$this->expectException( RuntimeException::class );
		adbp_sql_scope( $sql, 'adbp_123456abcdef_options' );
	}

	public static function rejected_statements() {
		return array_map( function ( $sql ) { return array( $sql ); }, array( 'UPDATE wp_options SET option_value=1', 'DELETE FROM wp_users', 'SELECT * FROM wp_options', 'INSERT IGNORE INTO adbp_123456abcdef_options VALUES (1)', 'SELECT * FROM adbp_123456abcdef_options JOIN wp_users', 'SELECT * FROM adbp_123456abcdef_options; DELETE FROM wp_options', 'DROP TABLE wp_options', 'TRUNCATE adbp_123456abcdef_options' ) );
	}

	public function test_owned_metadata_and_clone_statements_are_admitted() {
		foreach ( array( 'CREATE TABLE adbp_123456abcdef_options LIKE wp_options', 'SHOW INDEX FROM adbp_123456abcdef_options', 'SELECT option_value FROM adbp_123456abcdef_options WHERE option_name=1', 'DROP TABLE adbp_123456abcdef_options' ) as $sql ) {
			adbp_sql_scope( $sql, 'adbp_123456abcdef_options' );
			$this->addToAssertionCount( 1 );
		}
	}

	public function test_exact_core_upsert_is_reference_only_never_candidate_sql() {
		$table = 'adbp_123456abcdef_options';
		$sql = "INSERT INTO `$table` (`option_name`, `option_value`, `autoload`) VALUES ('alynt_drime_backups_dashboard_source_policies', 'a:0:{}', 'off') ON DUPLICATE KEY UPDATE `option_name` = VALUES(`option_name`), `option_value` = VALUES(`option_value`), `autoload` = VALUES(`autoload`)";
		adbp_sql_scope( $sql, $table, true );
		$this->addToAssertionCount( 1 );
		$this->expectException( RuntimeException::class );
		adbp_sql_scope( $sql, $table );
	}
}
