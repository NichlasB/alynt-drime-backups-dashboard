<?php
/**
 * Admin freshness headers tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

/**
 * Tests admin freshness helpers.
 */
class AdminPageFreshnessHeadersTest extends TestCase {
	/**
	 * Dashboard admin page sends no-cache headers when loaded.
	 *
	 * @return void
	 */
	public function test_admin_page_sends_no_cache_headers() {
		$GLOBALS['alynt_drime_backups_dashboard_nocache_headers_sent'] = 0;

		$admin_page = new Alynt_Drime_Backups_Dashboard_Admin_Page();
		$admin_page->send_no_cache_headers();

		$this->assertSame( 1, $GLOBALS['alynt_drime_backups_dashboard_nocache_headers_sent'] );
	}
}
