<?php
/**
 * Admin Sites-list context tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-sites-list-test-harness.php';

/**
 * Tests Sites-list row filtering helpers.
 */
class AdminPageSitesListTest extends TestCase {
	/**
	 * Revoked duplicate rows are hidden when an active row exists for the same origin.
	 *
	 * @return void
	 */
	public function test_superseded_revoked_duplicate_is_hidden() {
		$harness = new Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Test_Harness();
		$sites   = array(
			array(
				'id'                => 10,
				'expected_origin'   => 'https://internationalschoolofthehealingarts.com',
				'enrollment_status' => 'revoked',
			),
			array(
				'id'                => 11,
				'expected_origin'   => 'https://internationalschoolofthehealingarts.com/',
				'enrollment_status' => 'active',
			),
			array(
				'id'                => 12,
				'expected_origin'   => 'https://classes.internationalschoolofthehealingarts.com',
				'enrollment_status' => 'active',
			),
			array(
				'id'                => 13,
				'expected_origin'   => 'https://legacy.example.test',
				'enrollment_status' => 'revoked',
			),
		);

		$filtered = $harness->visible_sites( $sites );

		$this->assertSame(
			array( 11, 12, 13 ),
			array_map(
				static function ( $site ) {
					return $site['id'];
				},
				$filtered
			)
		);
	}

	/**
	 * Site contexts request explicit archive visibility modes.
	 *
	 * @return void
	 */
	public function test_site_contexts_filter_archived_visibility() {
		$harness = new Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Test_Harness();

		$visible = $harness->context_for( 'visible' );
		$this->assertSame( 'exclude', $harness->sites->calls[0]['archived'] );
		$this->assertSame(
			array( 1, 2 ),
			array_map(
				static function ( $site ) {
					return $site['id'];
				},
				$visible['sites']
			)
		);
		$this->assertSame( 1, $visible['attention_count'] );

		$archived = $harness->context_for( 'archived' );
		$this->assertSame( 'only', $harness->sites->calls[1]['archived'] );
		$this->assertSame(
			array( 3 ),
			array_map(
				static function ( $site ) {
					return $site['id'];
				},
				$archived['sites']
			)
		);
		$this->assertSame( 1, $harness->attention_count_for_test() );
	}

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
}
