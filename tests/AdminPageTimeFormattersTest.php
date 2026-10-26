<?php
/**
 * Absolute timestamp display regressions (no WordPress runtime).
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/includes/admin/traits/trait-admin-page-time-formatters.php';
require_once __DIR__ . '/support/admin-page-sites-list-wordpress-shims.php';

/** Tests absolute-only time display, not storage/protocol validation. */
class AdminPageTimeFormattersTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Time_Formatters;

	/**
	 * Relative, impossible and incomplete values must not acquire false freshness.
	 *
	 * @return void
	 */
	public function test_invalid_times_are_unavailable() {
		foreach ( array( '', 'tomorrow', 'now', '2026-02-31 12:00:00', '2026-01-01', '2026-01-01 24:00:00', '0000-01-01 12:00:00', '<script>bad</script>', '2026-01-01T12:00:00+99:99' ) as $value ) {
			$this->assertStringContainsString( 'Not available', $this->time_html( $value ), $value );
			$this->assertSame( '-', $this->datetime_label( $value ), $value );
		}
	}

	/**
	 * SQL UTC, ISO UTC, fractions and offsets preserve the same instant.
	 *
	 * @return void
	 */
	public function test_absolute_formats_preserve_their_instant() {
		foreach ( array( '2026-10-26 12:34:56', '2026-10-26T12:34:56Z', '2026-10-26T12:34:56.123456Z', '2026-10-26T14:34:56+02:00' ) as $value ) {
			$this->assertStringContainsString( 'datetime="2026-10-26T12:34:56+00:00"', $this->time_html( $value ) );
			$this->assertNotSame( '-', $this->datetime_label( $value ) );
		}
		$this->assertStringContainsString( '<time ', $this->time_html( '2024-02-29 12:00:00' ) );
	}
}
