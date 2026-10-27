<?php
/**
 * Actual list-query availability capture with an isolated wpdb collaborator.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/site-list-read-wpdb.php';

class SiteRepositoryListAvailabilityTest extends TestCase {
	/** @dataProvider outcomes */
	public function test_list_result_and_failure_signal_are_independent( $rows, $error, $expected_failure ) {
		$previous = $GLOBALS['wpdb'] ?? null;
		$database = new Alynt_Drime_Backups_Dashboard_Test_Site_List_Read_WPDB();
		$GLOBALS['wpdb'] = $database;
		try {
			$database->responses = array( array( $rows, $error ) );
			$repository = new Alynt_Drime_Backups_Dashboard_Site_Repository();
			$this->assertSame( is_array( $rows ) ? $rows : array(), $repository->all( array( 'archived' => 'only' ) ) );
			$this->assertSame( $expected_failure, $repository->last_list_read_failed() );
			$database->last_error = $error ? '' : 'unrelated later query';
			$this->assertSame( $expected_failure, $repository->last_list_read_failed() );
			$this->assertCount( 1, $database->queries );
			$this->assertStringContainsString( 'FROM tenant_42_alynt_drime_dashboard_sites', $database->queries[0] );
			$this->assertStringContainsString( 'archived_at IS NOT NULL', $database->queries[0] );
			$this->assertStringNotContainsString( 'polling_secret_ciphertext,', $database->queries[0] );
			$database->responses = array( array( array( array( 'id' => 77 ) ), '' ) );
			$this->assertSame( array( array( 'id' => 77 ) ), $repository->all() );
			$this->assertFalse( $repository->last_list_read_failed() );
		} finally {
			$GLOBALS['wpdb'] = $previous;
		}
	}

	public static function outcomes() {
		return array(
			'valid empty' => array( array(), '', false ),
			'valid populated' => array( array( array( 'id' => 7 ) ), '', false ),
			'empty error' => array( array(), 'private SQL', true ),
			'populated error' => array( array( array( 'id' => 7 ) ), 'private SQL', true ),
			'null error' => array( null, '', true ),
			'false error' => array( false, 'private SQL', true ),
		);
	}
}
