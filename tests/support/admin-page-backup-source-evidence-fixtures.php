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
	 * Creates the backup-source evidence harness.
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Source_Policy|null $source_policy Source policy.
	 * @return Alynt_Drime_Backups_Dashboard_Backup_Source_Evidence_Test_Harness
	 */
	private function backup_source_evidence_harness( $source_policy = null ) {
		return new Alynt_Drime_Backups_Dashboard_Backup_Source_Evidence_Test_Harness( $source_policy );
	}

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

	/**
	 * Builds a minimal site row for backup-source evidence tests.
	 *
	 * @param int $site_id Site ID.
	 * @return array<string,mixed>
	 */
	private function fixture_site( $site_id = 12 ) {
		return array(
			'id' => $site_id,
		);
	}

	/**
	 * Builds a source policy scoped to one fixture site/source.
	 *
	 * @param int    $site_id Site ID.
	 * @param string $source_key Source key.
	 * @param string $policy Source policy.
	 * @return Alynt_Drime_Backups_Dashboard_Source_Policy
	 */
	private function fixture_source_policy( $site_id, $source_key, $policy ) {
		return new Alynt_Drime_Backups_Dashboard_Source_Policy(
			array(
				(string) $site_id => array(
					$source_key => $policy,
				),
			)
		);
	}
}
