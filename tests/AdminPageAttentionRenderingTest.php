<?php
/**
 * Admin Attention and Sites summary rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-sites-list-test-harness.php';

/**
 * Tests Attention queue and Sites summary operator copy.
 */
class AdminPageAttentionRenderingTest extends TestCase {
	/**
	 * Attention empty state distinguishes no enrollment from healthy active rows.
	 *
	 * @return void
	 */
	public function test_attention_empty_state_explains_no_active_sites() {
		$harness                     = new Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Test_Harness();
		$harness->sites->visible_rows = array();

		$html = $harness->attention_html();

		$this->assertStringContainsString( 'No active sites enrolled yet', $html );
		$this->assertStringContainsString( 'Pair a client site before the Attention queue can show backup evidence that needs review.', $html );
		$this->assertStringNotContainsString( 'No active sites currently need attention', $html );
	}

	/**
	 * Attention empty state explains quiet active rows without implying a live poll.
	 *
	 * @return void
	 */
	public function test_attention_empty_state_explains_quiet_active_sites() {
		$harness                     = new Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Test_Harness();
		$harness->sites->visible_rows = array(
			array(
				'id'                => 7,
				'expected_origin'   => 'https://quiet.example.test',
				'enrollment_status' => 'active',
				'category'          => 'working',
				'archived_at'       => '',
			),
		);

		$html = $harness->attention_html();

		$this->assertStringContainsString( 'No active sites currently need attention', $html );
		$this->assertStringContainsString( 'Latest local snapshots for active sites are not classified as needs attention, not reporting, incompatible, or not configured.', $html );
		$this->assertStringNotContainsString( 'No active sites enrolled yet', $html );
	}

	/**
	 * Non-empty Attention queue explains that rows are based on stored snapshots.
	 *
	 * @return void
	 */
	public function test_attention_queue_explains_stored_snapshot_basis() {
		$harness = new Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Test_Harness();

		$html = $harness->attention_html();

		$this->assertStringContainsString( '1 site needs someone to review its latest evidence.', $html );
		$this->assertStringContainsString(
			'This queue uses the latest stored local snapshots. Use Check Now to request a fresh read-only status report for a specific site.',
			$html
		);
		$this->assertStringNotContainsString( 'No active sites currently need attention', $html );
	}

	/**
	 * Sites summary explains what the Attention metric includes.
	 *
	 * @return void
	 */
	public function test_sites_status_summary_explains_attention_metric() {
		$harness = new Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Test_Harness();
		$html    = $harness->status_summary_html(
			array(
				'working'         => 2,
				'pending'         => 0,
				'paused'          => 1,
				'incompatible'    => 1,
				'not_reporting'   => 1,
				'needs_attention' => 1,
				'not_configured'  => 1,
			),
			6,
			4
		);

		$this->assertStringContainsString( 'Site status summary', $html );
		$this->assertStringContainsString( 'Attention includes active records whose latest local snapshot is classified as needs attention, not reporting, incompatible, or not configured.', $html );
	}
}
