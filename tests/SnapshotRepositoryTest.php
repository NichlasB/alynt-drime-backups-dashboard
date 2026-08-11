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
	 * Rows returned by get_results().
	 *
	 * @var array<int,array<string,mixed>>
	 */
	public $result_rows = array();

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

	/**
	 * Returns configured result rows.
	 *
	 * @param string $query Query.
	 * @param string $output Output format.
	 * @return array<int,array<string,mixed>>
	 */
	public function get_results( $query, $output ) {
		unset( $output );
		$this->last_query = $query;

		return $this->result_rows;
	}
}

/**
 * Tests snapshot retention behavior.
 */
class SnapshotRepositoryTest extends TestCase {
	/**
	 * Recent history is bounded and excludes raw payload JSON.
	 *
	 * @return void
	 */
	public function test_recent_history_is_bounded_and_selects_summary_fields_only() {
		global $wpdb;

		$previous_wpdb     = $wpdb;
		$wpdb              = new Alynt_Drime_Backups_Dashboard_Test_Snapshot_WPDB();
		$wpdb->result_rows = array(
			array(
				'id'                => 9,
				'dashboard_site_id' => 42,
				'overall_status'    => 'working',
			),
		);
		$repository        = new Alynt_Drime_Backups_Dashboard_Snapshot_Repository();

		$rows = $repository->recent_for_site( 42, 999 );

		$this->assertCount( 1, $rows );
		$this->assertSame( array( 42, 50 ), $wpdb->prepared_args );
		$this->assertStringContainsString( 'ORDER BY observed_at DESC, id DESC', $wpdb->last_query );
		$this->assertStringContainsString( 'LIMIT %d', $wpdb->last_query );
		$this->assertStringNotContainsString( 'payload_json', $wpdb->last_query );

		$wpdb = $previous_wpdb;
	}

	/**
	 * Invalid site IDs do not run a history query.
	 *
	 * @return void
	 */
	public function test_recent_history_rejects_invalid_site_id_without_query() {
		global $wpdb;

		$previous_wpdb = $wpdb;
		$wpdb          = new Alynt_Drime_Backups_Dashboard_Test_Snapshot_WPDB();
		$repository    = new Alynt_Drime_Backups_Dashboard_Snapshot_Repository();

		$this->assertSame( array(), $repository->recent_for_site( 0 ) );
		$this->assertSame( '', $wpdb->last_query );

		$wpdb = $previous_wpdb;
	}

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
