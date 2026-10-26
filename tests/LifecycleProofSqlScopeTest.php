<?php
/**
 * Isolated generated-SQL scope checks, not WordPress lifecycle proof.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;
require_once dirname( __DIR__ ) . '/scripts/manual/lifecycle-proof-sql-scope.php';

/** Tests the fixed owned lifecycle SQL assertion. */
class LifecycleProofSqlScopeTest extends TestCase {
	/**
	 * Real owned statement shapes remain eligible, including upsert and purge.
	 *
	 * @return void
	 */
	public function test_owned_shapes_are_admitted() {
		foreach ( array( 'SELECT * FROM owned_options', 'DELETE FROM owned_options WHERE option_name = 1', 'INSERT INTO owned_options VALUES (1) ON DUPLICATE KEY UPDATE option_value = 2', 'DROP TABLE IF EXISTS owned_sites', 'CREATE TABLE IF NOT EXISTS owned_sites (id int)', 'UPDATE owned_options SET option_value = 1', 'DESCRIBE owned_sites' ) as $sql ) {
			adb_lifecycle_assert_sql_scope( $sql, array( 'owned_options', 'owned_sites' ) );
			$this->addToAssertionCount( 1 );
		}
	}

	/**
	 * Each mutating/non-owned or mixed-target shape must actually be rejected.
	 *
	 * @return void
	 */
	public function test_non_owned_shapes_are_rejected() {
		foreach ( array( 'SELECT * FROM wp_options', 'DELETE FROM wp_options WHERE option_name = 1', 'UPDATE wp_options SET option_value = 1', 'DROP TABLE IF EXISTS wp_options', 'SELECT * FROM owned_options JOIN wp_users ON 1 = 1', 'TRUNCATE owned_options', 'GRANT SELECT ON owned_options TO x' ) as $sql ) {
			$rejected = false;
			try {
				adb_lifecycle_assert_sql_scope( $sql, array( 'owned_options' ) );
			} catch ( RuntimeException $error ) {
				$rejected = true;
			}
			$this->assertTrue( $rejected, $sql );
		}
	}
}
