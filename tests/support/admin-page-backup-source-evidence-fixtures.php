<?php
/**
 * Admin backup source evidence test fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Fixture helpers for admin backup source evidence tests.
 */
trait Alynt_Drime_Backups_Dashboard_Backup_Source_Evidence_Test_Fixtures {
	/**
	 * Loads the validated uploader-shaped schema-1 fixture.
	 *
	 * @return array<string,mixed>
	 */
	private function fixture_payload() {
		$fixture = file_get_contents( dirname( __DIR__ ) . '/fixtures/uploader-backup-sources-schema1.json' );
		$payload = json_decode( (string) $fixture, true );

		$this->assertIsArray( $payload );

		return $payload;
	}
}
