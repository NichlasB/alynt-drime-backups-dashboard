<?php
/**
 * Snapshot repository tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/includes/class-storage.php';
require_once dirname( __DIR__ ) . '/includes/class-snapshot-repository.php';

/**
 * Fake wpdb for snapshot repository tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Snapshot_WPDB {
	/**
	 * Table prefix.
	 *
	 * @var string
	 */
	public $prefix = 'wp_';

	/**
	 * Prepared arguments.
	 *
	 * @var array<int,mixed>
	 */
	public $prepared_args = array();

	/**
	 * Last query.
	 *
	 * @var string
	 */
	public $last_query = '';

	/**
	 * Prepares a query.
	 *
	 * @param string $query Query.
	 * @param mixed  ...$args Arguments.
	 * @return string
	 */
	public function prepare( $query, ...$args ) {
		$this->prepared_args = $args;

		return $query;
	}

	/**
	 * Runs a query.
	 *
	 * @param string $query Query.
	 * @return int
	 */
	public function query( $query ) {
		$this->last_query = $query;

		return 17;
	}
}

/**
 * Tests snapshot retention behavior.
 */
class SnapshotRepositoryTest extends TestCase {
	/**
	 * Cleanup preserves each site's latest snapshot.
	 *
	 * @return void
	 */
	public function test_cleanup_retention_preserves_latest_snapshot_per_site() {
		global $wpdb;

		$previous_wpdb = $wpdb;
		$wpdb          = new Alynt_Drime_Backups_Dashboard_Test_Snapshot_WPDB();
		$repository    = new Alynt_Drime_Backups_Dashboard_Snapshot_Repository();

		$deleted = $repository->cleanup_retention( 30, 500 );

		$this->assertSame( 17, $deleted );
		$this->assertStringContainsString( 'DELETE FROM wp_alynt_drime_dashboard_snapshots', $wpdb->last_query );
		$this->assertStringContainsString( 'MAX(id) AS latest_id', $wpdb->last_query );
		$this->assertStringContainsString( 'GROUP BY dashboard_site_id', $wpdb->last_query );
		$this->assertStringContainsString( 'id NOT IN', $wpdb->last_query );
		$this->assertSame( 500, $wpdb->prepared_args[1] );

		$wpdb = $previous_wpdb;
	}
}
