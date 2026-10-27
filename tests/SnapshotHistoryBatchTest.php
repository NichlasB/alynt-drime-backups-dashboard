<?php
/**
 * Isolated SQL-construction/result-grouping contracts, not physical SQL proof.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/snapshot-history-batch-wpdb-double.php';

/**
 * Verifies bounded, independently limited, summary-only history batch reads.
 */
class SnapshotHistoryBatchTest extends TestCase {

	/** @var mixed Previous database collaborator. */
	private $previous_wpdb;

	/** @var Alynt_Drime_Backups_Dashboard_Test_History_Batch_WPDB Read capture. */
	private $database;

	/** @var Alynt_Drime_Backups_Dashboard_Snapshot_Repository Subject. */
	private $repository;

	protected function setUp(): void {
		$this->previous_wpdb = $GLOBALS['wpdb'] ?? null;
		$this->database = new Alynt_Drime_Backups_Dashboard_Test_History_Batch_WPDB();
		$GLOBALS['wpdb'] = $this->database;
		$this->repository = new Alynt_Drime_Backups_Dashboard_Snapshot_Repository();
	}

	protected function tearDown(): void {
		$GLOBALS['wpdb'] = $this->previous_wpdb;
	}

	public function test_union_limits_each_site_and_orders_the_final_result_without_payloads() {
		$this->database->responses = array( array(
			array( 'id' => '4', 'dashboard_site_id' => '2', 'observed_at' => '2026-10-27 10:00:00' ),
			array( 'id' => '3', 'dashboard_site_id' => '2', 'observed_at' => '2026-10-27 10:00:00' ),
			array( 'id' => '99', 'dashboard_site_id' => '2', 'observed_at' => '2026-10-26 10:00:00' ),
			array( 'id' => '20', 'dashboard_site_id' => '7', 'observed_at' => '2026-10-27 11:00:00' ),
		) );
		$histories = $this->repository->recent_by_site_ids( array( 7, 2, 7, 0 ), 10 );
		$this->assertCount( 1, $this->database->queries );
		$this->assertSame( array( 7, 10, 2, 10 ), $this->database->prepared[0]['args'] );
		$sql = $this->database->queries[0];
		$this->assertSame( 2, substr_count( $sql, 'WHERE dashboard_site_id = %d' ) );
		$this->assertSame( 2, substr_count( $sql, 'ORDER BY observed_at DESC, id DESC' ) );
		$this->assertSame( 2, substr_count( $sql, 'LIMIT %d)' ) );
		$this->assertStringContainsString( ') UNION ALL (SELECT ', $sql );
		$this->assertStringEndsWith( 'ORDER BY dashboard_site_id ASC, observed_at DESC, id DESC', $sql );
		$this->assertSame( 2, substr_count( $sql, 'SELECT id, dashboard_site_id, schema_version, observed_at, overall_status, queue_count, uploaded_count, failed_count, active_upload, warning_count, cron_status' ) );
		$this->assertStringNotContainsString( '*', $sql );
		$this->assertStringNotContainsString( 'payload_json', $sql );
		$this->assertStringNotContainsString( 'JOIN', $sql );
		$this->assertSame( array( '4', '3', '99' ), array_column( $histories[2], 'id' ) );
		$this->assertSame( array( '20' ), array_column( $histories[7], 'id' ) );
		$this->assertSame( array( 7, 2 ), array_keys( $histories ) );
	}

	/** @dataProvider batch_sizes */
	public function test_query_count_is_bounded_by_fifty_unique_sites( $sites, $queries, $last_size ) {
		$this->database->responses = array_fill( 0, $queries, array() );
		$histories = $this->repository->recent_by_site_ids( range( 1, $sites ) );
		$this->assertCount( $queries, $this->database->queries );
		$this->assertCount( $sites, $histories );
		foreach ( $this->database->prepared as $index => $statement ) {
			$size = $index === $queries - 1 ? $last_size : 50;
			$this->assertCount( $size * 2, $statement['args'] );
			$this->assertSame( $size, substr_count( $statement['sql'], 'LIMIT %d' ) );
			$this->assertSame( array_fill( 0, $size, 10 ), array_values( array_filter( $statement['args'], static function ( $value, $key ) { return $key % 2 === 1; }, ARRAY_FILTER_USE_BOTH ) ) );
		}
		if ( $last_size === 1 ) {
			$this->assertStringStartsWith( 'SELECT ', $this->database->queries[ $queries - 1 ] );
			$this->assertStringEndsWith( 'LIMIT %d', $this->database->queries[ $queries - 1 ] );
		}
	}

	public function batch_sizes() {
		return array( array( 1, 1, 1 ), array( 50, 1, 50 ), array( 51, 2, 1 ), array( 100, 2, 50 ), array( 500, 10, 50 ) );
	}

	/** @dataProvider limits */
	public function test_limit_clamp_matches_single_site_history( $input, $expected ) {
		$this->repository->recent_by_site_ids( array( 3 ), $input );
		$this->assertSame( array( 3, $expected ), $this->database->prepared[0]['args'] );
	}

	public function limits() {
		return array( array( 0, 1 ), array( -10, 1 ), array( 999, 50 ), array( 10, 10 ) );
	}

	public function test_empty_and_zero_ids_do_not_prepare_or_read() {
		$this->assertSame( array(), $this->repository->recent_by_site_ids( array() ) );
		$this->assertSame( array(), $this->repository->recent_by_site_ids( array( 0, '0', null, '' ) ) );
		$this->assertSame( array(), $this->database->prepared );
		$this->assertSame( array(), $this->database->queries );
	}

	public function test_normalization_deduplicates_ids_before_batching() {
		$this->repository->recent_by_site_ids( array( '2', 2, -2, '3 OR 1=1', 0 ) );
		$this->assertSame( array( 2, 10, 3, 10 ), $this->database->prepared[0]['args'] );
		$this->assertStringNotContainsString( 'OR 1=1', $this->database->queries[0] );
	}

	public function test_failed_batch_stays_empty_and_later_batch_is_read_without_n_plus_one_retry() {
		$row = array( 'id' => 9, 'dashboard_site_id' => 51 );
		$this->database->responses = array( false, array( $row ) );
		$result = $this->repository->recent_by_site_ids( range( 1, 51 ) );
		$this->assertCount( 2, $this->database->queries );
		$this->assertSame( array(), $result[1] );
		$this->assertSame( array(), $result[50] );
		$this->assertSame( array( $row ), $result[51] );
	}

	public function test_results_cannot_populate_another_batch_or_unrequested_site() {
		$this->database->responses = array( array(
			array( 'id' => 1, 'dashboard_site_id' => 51 ),
			array( 'id' => 2, 'dashboard_site_id' => 999 ),
		), null );
		$result = $this->repository->recent_by_site_ids( range( 1, 51 ) );
		$this->assertCount( 51, $result );
		$this->assertSame( array(), $result[51] );
		$this->assertArrayNotHasKey( 999, $result );
	}
}
