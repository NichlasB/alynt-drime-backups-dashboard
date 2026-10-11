<?php
/**
 * Admin backup source evidence detail rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-backup-source-evidence-test-harness.php';

/**
 * Tests source-level backup evidence detail and policy rendering helpers.
 */
class AdminPageBackupSourceEvidenceDetailTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Backup_Source_Evidence_Test_Fixtures;

	/**
	 * Site detail evidence includes backup/package and upload timestamps separately.
	 *
	 * @return void
	 */
	public function test_detail_evidence_separates_backup_package_and_upload_timestamps() {
		$harness = $this->backup_source_evidence_harness();
		$html    = $harness->detail_html( $this->fixture_payload() );

		$this->assertSame( 2, substr_count( $html, 'Latest backup/package' ) );
		$this->assertSame( 2, substr_count( $html, 'Latest upload' ) );
		$this->assertSame( 2, substr_count( $html, 'Expected freshness' ) );
		$this->assertSame( 2, substr_count( $html, 'Operator summary' ) );
		$this->assertStringContainsString( 'within 9 days (detected WPvivid schedule)', $html );
		$this->assertStringContainsString( 'Latest WPvivid activity', $html );
		$this->assertStringContainsString( 'Local WPvivid ZIPs', $html );
		$this->assertStringContainsString( 'Source evidence is reported by the client uploader as a redacted operational hint.', $html );
	}

	/**
	 * WPvivid evidence inside the dashboard policy window is labeled separately from stale.
	 *
	 * @return void
	 */
	public function test_wpvivid_stale_inside_policy_displays_within_policy() {
		$payload = $this->fixture_payload();

		$payload['backup_sources']['wpvivid']['freshness_status']          = 'stale';
		$payload['backup_sources']['wpvivid']['latest_upload_age_seconds'] = 172800;
		$payload['backup_sources']['wpvivid']['freshness_window_seconds']  = 129600;

		$harness      = $this->backup_source_evidence_harness();
		$compact_html = $harness->compact_html( $payload );
		$detail_html  = $harness->detail_html( $payload );

		$this->assertStringContainsString( 'Within policy', $compact_html );
		$this->assertStringContainsString( 'expected ≤9 days', $compact_html );
		$this->assertStringContainsString( 'The uploader marked this source stale, but the latest upload is still inside the dashboard freshness policy.', $compact_html );
		$this->assertStringContainsString( 'Within policy', $detail_html );
		$this->assertStringContainsString( 'within 9 days (detected WPvivid schedule)', $detail_html );
		$this->assertStringContainsString( 'The uploader marked this source stale, but the latest upload is still inside the dashboard freshness policy.', $detail_html );
	}

	/**
	 * External optional WPvivid policy is visible in compact and detail evidence.
	 *
	 * @return void
	 */
	public function test_external_optional_wpvivid_policy_displays_explicitly() {
		$payload = $this->fixture_payload();
		$site    = $this->fixture_site();

		$harness = $this->backup_source_evidence_harness( $this->fixture_source_policy( 12, 'wpvivid', 'external_optional' ) );

		$compact_html = $harness->compact_html( $payload, $site );
		$detail_html  = $harness->detail_html( $payload, $site );

		$this->assertStringContainsString( 'External / optional', $compact_html );
		$this->assertStringContainsString( 'external / optional', $compact_html );
		$this->assertStringContainsString( 'This source is marked external/optional, so Alynt-uploaded evidence is not required on this dashboard.', $compact_html );
		$this->assertStringContainsString( 'External / optional', $detail_html );
		$this->assertStringContainsString( 'external / optional on this dashboard', $detail_html );
		$this->assertStringContainsString( 'This source is marked external/optional, so Alynt-uploaded evidence is not required on this dashboard.', $detail_html );
	}
}
