<?php
/**
 * Storage schema tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

/**
 * Tests dashboard-owned storage schema declarations.
 */
class StorageSchemaTest extends TestCase {
	/**
	 * Remote action retention cleanup has a matching index.
	 *
	 * @return void
	 */
	public function test_actions_table_has_completed_at_cleanup_index(): void {
		$source = file_get_contents( dirname( __DIR__ ) . '/includes/class-storage.php' );

		$this->assertIsString( $source );
		$this->assertStringContainsString( "const SCHEMA_VERSION        = '6';", $source );
		$this->assertStringContainsString( 'KEY completed_at (completed_at, id)', $source );
	}
}
