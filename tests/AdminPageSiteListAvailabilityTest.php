<?php
/**
 * Site-list availability rendering through the actual repository and UI traits.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-sites-list-test-harness.php';
require_once __DIR__ . '/support/site-list-read-wpdb.php';

class AdminPageSiteListAvailabilityTest extends TestCase {
	private $previous_database;
	private $previous_get;
	private $database;

	protected function setUp(): void {
		$this->previous_database = $GLOBALS['wpdb'] ?? null;
		$this->previous_get = $_GET;
		$this->database = new Alynt_Drime_Backups_Dashboard_Test_Site_List_Read_WPDB();
		$GLOBALS['wpdb'] = $this->database;
		$_GET = array();
	}

	protected function tearDown(): void {
		$GLOBALS['wpdb'] = $this->previous_database;
		$_GET = $this->previous_get;
	}

	private function harness() {
		$harness = new Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Test_Harness();
		$harness->sites = new Alynt_Drime_Backups_Dashboard_Site_Repository();
		return $harness;
	}

	/** @dataProvider views */
	public function test_failed_read_never_renders_a_successful_empty_state( $archived, $attention ) {
		$_GET = $archived ? array( 'archived' => '1' ) : array();
		$this->database->responses = array_fill( 0, 2, array( array(), 'private SQL /secret/path credential=hidden' ) );
		$harness = $this->harness();
		$html = $attention ? $harness->attention_html() : $harness->sites_html();
		$this->assertStringContainsString( 'Site List Is Unavailable', $html );
		$this->assertStringContainsString( 'role="alert"', $html );
		foreach ( array( 'No Client Sites Are Paired Yet', 'No Archived Local Records', 'No active sites enrolled yet', 'private SQL', '/secret/path', 'credential=hidden', 'adbd-metrics' ) as $absent ) {
			$this->assertStringNotContainsString( $absent, $html );
		}
		$this->assertCount( 1, $this->database->queries );
		$this->assertSame( array(), $harness->snapshots->calls );
	}

	public static function views() {
		return array( array( false, false ), array( true, false ), array( false, true ) );
	}

	public function test_genuine_empty_lists_keep_their_existing_guidance_and_zero_badge() {
		$this->database->responses = array_fill( 0, 2, array( array(), '' ) );
		$harness = $this->harness();
		$this->assertStringContainsString( 'No Client Sites Are Paired Yet', $harness->sites_html() );
		$this->assertStringContainsString( 'No active sites enrolled yet', $harness->attention_html() );
		$this->assertSame( 0, $harness->attention_count_for_test() );
		$this->assertStringContainsString( 'adbd-tab-count', $harness->tabs_html() );
	}

	public function test_failed_context_remains_unavailable_after_an_unrelated_success() {
		$this->database->responses = array( array( array(), 'private SQL' ), array( array(), '' ) );
		$harness = $this->harness();
		$harness->context_for( 'visible' );
		$harness->context_for( 'archived' );
		$this->assertNull( $harness->attention_count_for_test() );
		$this->assertStringNotContainsString( 'adbd-tab-count', $harness->tabs_html() );
		$this->assertStringContainsString( 'Site List Is Unavailable', $harness->attention_html() );
		$this->assertCount( 2, $this->database->queries );
	}

	public function test_failed_archived_count_does_not_hide_valid_visible_rows() {
		$rows = ( new Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Test_Sites() )->all();
		$this->database->responses = array( array( $rows, '' ), array( array(), 'private SQL' ) );
		$harness = $this->harness();
		$html = $harness->sites_html();
		$this->assertStringContainsString( 'active.example.test', $html );
		$this->assertStringContainsString( 'Archived record count is unavailable.', $html );
		$this->assertStringNotContainsString( 'View Archived Records (0)', $html );
		$this->assertStringNotContainsString( 'Site List Is Unavailable', $html );
		$this->assertSame( 1, $harness->attention_count_for_test() );
		$this->assertCount( 2, $this->database->queries );
	}

	public function test_new_request_recovers_and_successful_archived_count_is_preserved() {
		$this->database->responses = array( array( array(), 'private SQL' ) );
		$this->assertStringContainsString( 'Site List Is Unavailable', $this->harness()->sites_html() );
		$rows = ( new Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Test_Sites() )->all();
		$this->database->responses = array( array( $rows, '' ), array( array( $rows[0] ), '' ) );
		$harness = $this->harness();
		$html = $harness->sites_html();
		$this->assertStringContainsString( 'View Archived Record (1)', $html );
		$this->assertStringContainsString( 'active.example.test', $html );
		$this->assertStringNotContainsString( 'Site List Is Unavailable', $html );
		$this->assertSame( 1, $harness->attention_count_for_test() );
	}
}
