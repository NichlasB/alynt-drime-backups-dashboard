<?php
/**
 * Admin backup source evidence rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-backup-source-evidence-test-harness.php';

/**
 * Tests source-level backup evidence rendering helpers.
 */
class AdminPageBackupSourceEvidenceTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Backup_Source_Evidence_Test_Fixtures;

	/**
	 * Sites table compact evidence includes at-a-glance source summaries.
	 *
	 * @return void
	 */
	public function test_sites_table_compact_evidence_includes_source_timestamps() {
		$harness = $this->backup_source_evidence_harness();
		$payload = $this->fixture_payload();

		$payload['backup_sources']['server']['latest_upload_age_seconds']  = 53442;
		$payload['backup_sources']['wpvivid']['latest_upload_age_seconds'] = 64152;

		$html = $harness->compact_html( $payload );

		$this->assertStringContainsString( 'Server runner / generic outbox', $html );
		$this->assertStringContainsString( 'WPvivid', $html );
		$this->assertStringContainsString( 'Backups:', $html );
		$this->assertStringContainsString( 'On schedule', $html );
		$this->assertStringContainsString( '14 hours ago', $html );
		$this->assertStringContainsString( '17 hours ago', $html );
		$this->assertStringNotContainsString( '53442 seconds ago', $html );
		$this->assertStringNotContainsString( '64152 seconds ago', $html );
		$this->assertStringContainsString( '3 sets', $html );
		$this->assertStringContainsString( '1 set', $html );
		$this->assertStringContainsString( 'expected ≤36 hours', $html );
		$this->assertStringContainsString( 'expected ≤9 days', $html );
		$this->assertStringContainsString( 'The latest upload is fresh; queued packages are waiting to upload.', $html );
		$this->assertStringNotContainsString( 'Latest backup/package', $html );
		$this->assertStringNotContainsString( 'Latest upload', $html );
		$this->assertStringNotContainsString( 'Expected:', $html );
		$this->assertStringNotContainsString( 'WPvivid backup log observed', $html );
		$this->assertStringNotContainsString( '<time datetime=', $html );
	}

	/**
	 * Stale and missing source evidence show compact warning summaries.
	 *
	 * @return void
	 */
	public function test_attention_source_states_show_direct_operator_reasons() {
		$payload = $this->fixture_payload();

		$payload['backup_sources']['server']['freshness_status']          = 'stale';
		$payload['backup_sources']['server']['latest_upload_age_seconds'] = 200000;
		$payload['backup_sources']['server']['warnings']                  = array(
			array(
				'code'    => 'source_latest_upload_stale',
				'message' => 'The latest uploaded backup evidence is older than expected.',
			),
		);
		$payload['backup_sources']['wpvivid']['freshness_status']         = 'no_upload_evidence';
		$payload['backup_sources']['wpvivid']['has_upload_evidence']      = false;
		$payload['backup_sources']['wpvivid']['warnings']                 = array();

		$harness = $this->backup_source_evidence_harness();
		$html    = $harness->compact_html( $payload );

		$this->assertStringContainsString( 'Backups:', $html );
		$this->assertStringContainsString( 'Server overdue', $html );
		$this->assertStringContainsString( 'Stale', $html );
		$this->assertStringContainsString( 'No upload evidence', $html );
		$this->assertStringContainsString( 'The latest uploaded backup evidence is older than expected.', $html );
		$this->assertStringContainsString( 'No Alynt-uploaded backup evidence is reported for this source.', $html );
	}

}
