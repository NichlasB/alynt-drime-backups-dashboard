<?php
/**
 * Current snapshot identity versus chronological history.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/snapshot-selection-wpdb-double.php';

/**
 * Exercises actual repository methods over a query-sensitive isolated row model.
 */
class SnapshotLatestSelectionTest extends TestCase {
	/** @var mixed Prior global database double. */
	private $previous_wpdb;
	/** @var Alynt_Drime_Backups_Dashboard_Test_Snapshot_Selection_WPDB Row model. */
	private $db;
	/** @var Alynt_Drime_Backups_Dashboard_Snapshot_Repository Actual repository. */
	private $repository;

	protected function setUp(): void {
		global $wpdb;
		$this->previous_wpdb = $wpdb;
		$this->db            = new Alynt_Drime_Backups_Dashboard_Test_Snapshot_Selection_WPDB();
		$wpdb                = $this->db;
		$this->repository    = new Alynt_Drime_Backups_Dashboard_Snapshot_Repository();
	}

	protected function tearDown(): void {
		global $wpdb;
		$wpdb = $this->previous_wpdb;
	}

	/**
	 * Both current readers select insertion identity, not observation chronology.
	 *
	 * @dataProvider observation_orders
	 * @param string $newer_time Newer insertion's observation timestamp.
	 * @return void
	 */
	public function test_current_readers_agree_for_reversed_equal_and_increasing_times( $newer_time ) {
		$this->db->rows = array( $this->row( 101, 42, $newer_time ), $this->row( 999, 7 ), $this->row( 100, 42 ) );
		$current        = $this->repository->latest_for_site( '42' );
		$this->assertSame( 101, $current['id'] );
		$this->assertSame( array( 42 ), $this->db->prepared_args );
		$this->assertStringContainsString( 'WHERE dashboard_site_id = %d ORDER BY id DESC LIMIT 1', $this->db->last_query );
		$this->assertSame( array( 'id' => 101 ), $current['decoded_payload'] );
		$fleet = $this->repository->latest_by_site_ids( array( 42, 7 ) );
		$this->assertSame( $current, $fleet[42] );
		$this->assertSame( 999, $fleet[7]['id'] );
	}

	/**
	 * @return array<string,array<int,string>> Timestamp orders.
	 */
	public function observation_orders() {
		return array(
			'regressed' => array( '2026-08-01 00:00:00' ),
			'tied'      => array( '2026-08-02 00:00:00' ),
			'advanced'  => array( '2026-08-03 00:00:00' ),
		);
	}

	public function test_missing_site_never_falls_back_to_other_sites_and_bad_json_decodes_empty() {
		$this->db->rows = array( $this->row( 999, 7 ) );
		$this->assertNull( $this->repository->latest_for_site( 42 ) );
		$this->assertNull( $this->repository->latest_for_site( 0 ) );
		$this->assertSame( array(), $this->repository->latest_by_site_ids( array( 42 ) ) );
		$malformed                 = $this->row( 101, 42 );
		$malformed['payload_json'] = '{';
		$this->db->rows[]          = $malformed;
		$this->assertSame( array(), $this->repository->latest_for_site( 42 )['decoded_payload'] );
	}

	public function test_history_remains_chronological_and_summary_only_after_current_selection() {
		$this->db->rows = array( $this->row( 101, 42, '2026-08-01 00:00:00' ), $this->row( 999, 7 ), $this->row( 100, 42 ) );
		$this->assertSame( 101, $this->repository->latest_for_site( 42 )['id'] );
		$history = $this->repository->recent_for_site( 42, 2 );
		$this->assertSame( array( 100, 101 ), array_column( $history, 'id' ) );
		$this->assertSame( array( 42, 2 ), $this->db->prepared_args );
		$this->assertStringContainsString( 'ORDER BY observed_at DESC, id DESC', $this->db->last_query );
		$this->assertStringNotContainsString( 'payload_json', $this->db->last_query );
		$this->assertArrayNotHasKey( 'payload_json', $history[0] );
	}

	/**
	 * @param int    $id Row ID.
	 * @param int    $site_id Site ID.
	 * @param string $observed_at Observation timestamp.
	 * @return array<string,mixed>
	 */
	private function row( $id, $site_id, $observed_at = '2026-08-02 00:00:00' ) {
		return array(
			'id'                => $id,
			'dashboard_site_id' => $site_id,
			'observed_at'       => $observed_at,
			'payload_json'      => wp_json_encode( array( 'id' => $id ) ),
		);
	}
}
