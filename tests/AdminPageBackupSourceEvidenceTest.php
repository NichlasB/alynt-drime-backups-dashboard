<?php
/**
 * Admin backup source evidence rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/includes/traits/trait-admin-page-time-formatters.php';
require_once dirname( __DIR__ ) . '/includes/class-source-policy.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-admin-page-backup-source-evidence.php';

/**
 * Tests source-level backup evidence rendering helpers.
 */
class AdminPageBackupSourceEvidenceTest extends TestCase {
	/**
	 * Sites table compact evidence includes at-a-glance source timestamps.
	 *
	 * @return void
	 */
	public function test_sites_table_compact_evidence_includes_source_timestamps() {
		$harness = new Alynt_Drime_Backups_Dashboard_Backup_Source_Evidence_Test_Harness();
		$html    = $harness->compact_html( $this->fixture_payload() );

		$this->assertStringContainsString( 'Server runner / generic outbox', $html );
		$this->assertStringContainsString( 'WPvivid', $html );
		$this->assertStringContainsString( '3 current package sets', $html );
		$this->assertStringContainsString( '1 current package set', $html );
		$this->assertSame( 2, substr_count( $html, 'Latest backup/package' ) );
		$this->assertSame( 2, substr_count( $html, 'Latest upload' ) );
		$this->assertSame( 2, substr_count( $html, 'Expected' ) );
		$this->assertStringContainsString( 'WPvivid activity', $html );
		$this->assertStringContainsString( 'WPvivid backup log observed', $html );
		$this->assertStringContainsString( '<time datetime=', $html );
	}

	/**
	 * Site detail evidence includes backup/package and upload timestamps separately.
	 *
	 * @return void
	 */
	public function test_detail_evidence_separates_backup_package_and_upload_timestamps() {
		$harness = new Alynt_Drime_Backups_Dashboard_Backup_Source_Evidence_Test_Harness();
		$html    = $harness->detail_html( $this->fixture_payload() );

		$this->assertSame( 2, substr_count( $html, 'Latest backup/package' ) );
		$this->assertSame( 2, substr_count( $html, 'Latest upload' ) );
		$this->assertSame( 2, substr_count( $html, 'Expected freshness' ) );
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

		$harness      = new Alynt_Drime_Backups_Dashboard_Backup_Source_Evidence_Test_Harness();
		$compact_html = $harness->compact_html( $payload );
		$detail_html  = $harness->detail_html( $payload );

		$this->assertStringContainsString( 'Within policy', $compact_html );
		$this->assertStringContainsString( 'within 9 days (detected WPvivid schedule)', $compact_html );
		$this->assertStringContainsString( 'Within policy', $detail_html );
		$this->assertStringContainsString( 'within 9 days (detected WPvivid schedule)', $detail_html );
	}

	/**
	 * External optional WPvivid policy is visible in compact and detail evidence.
	 *
	 * @return void
	 */
	public function test_external_optional_wpvivid_policy_displays_explicitly() {
		$payload = $this->fixture_payload();
		$site    = array(
			'id' => 12,
		);

		$harness = new Alynt_Drime_Backups_Dashboard_Backup_Source_Evidence_Test_Harness(
			new Alynt_Drime_Backups_Dashboard_Source_Policy(
				array(
					'12' => array(
						'wpvivid' => 'external_optional',
					),
				)
			)
		);

		$compact_html = $harness->compact_html( $payload, $site );
		$detail_html  = $harness->detail_html( $payload, $site );

		$this->assertStringContainsString( 'External / optional', $compact_html );
		$this->assertStringContainsString( 'external / optional on this dashboard', $compact_html );
		$this->assertStringContainsString( 'External / optional', $detail_html );
		$this->assertStringContainsString( 'external / optional on this dashboard', $detail_html );
	}

	/**
	 * Loads the validated uploader-shaped schema-1 fixture.
	 *
	 * @return array<string,mixed>
	 */
	private function fixture_payload() {
		$fixture = file_get_contents( __DIR__ . '/fixtures/uploader-backup-sources-schema1.json' );
		$payload = json_decode( (string) $fixture, true );

		$this->assertIsArray( $payload );

		return $payload;
	}
}

/**
 * Harness exposing private admin rendering helpers for focused tests.
 */
class Alynt_Drime_Backups_Dashboard_Backup_Source_Evidence_Test_Harness {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Time_Formatters;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Backup_Source_Evidence;

	/**
	 * Source policy.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Source_Policy
	 */
	private $source_policy;

	/**
	 * Constructor.
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Source_Policy|null $source_policy Source policy.
	 */
	public function __construct( $source_policy = null ) {
		$this->source_policy = $source_policy instanceof Alynt_Drime_Backups_Dashboard_Source_Policy ? $source_policy : new Alynt_Drime_Backups_Dashboard_Source_Policy();
	}

	/**
	 * Exposes compact source evidence markup.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @param array<string,mixed> $site Site row.
	 * @return string
	 */
	public function compact_html( array $payload, array $site = array() ) {
		return $this->backup_sources_compact_html( $payload, $site );
	}

	/**
	 * Exposes detail source evidence markup.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @param array<string,mixed> $site Site row.
	 * @return string
	 */
	public function detail_html( array $payload, array $site = array() ) {
		ob_start();
		$this->render_backup_sources_detail( $payload, $site );
		return (string) ob_get_clean();
	}

	/**
	 * Minimal detail-list renderer used by the detail helper.
	 *
	 * @param string $label Label.
	 * @param string $value Value.
	 * @param bool   $raw Whether value is pre-escaped markup.
	 * @return void
	 */
	private function render_detail_item( $label, $value, $raw = false ) {
		echo '<dt>' . esc_html( $label ) . '</dt><dd>';
		echo $raw ? $value : esc_html( $value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Test harness preserves helper-provided markup when requested.
		echo '</dd>';
	}
}
