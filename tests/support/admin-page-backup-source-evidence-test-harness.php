<?php
/**
 * Admin backup source evidence test harness.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-time-formatters.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-source-policy.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-backup-source-timestamp-helpers.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-backup-source-evidence-helpers.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-backup-source-compact-helpers.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-backup-source-operator-helpers.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-backup-source-policy-helpers.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-backup-source-evidence.php';

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

/**
 * Harness exposing private admin rendering helpers for focused tests.
 */
class Alynt_Drime_Backups_Dashboard_Backup_Source_Evidence_Test_Harness {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Time_Formatters;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Backup_Source_Timestamp_Helpers;
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
