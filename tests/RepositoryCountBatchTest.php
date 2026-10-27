<?php
/** Isolated grouped-count query construction/result/fallback contracts. */
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/support/count-batch-wpdb-double.php';

class RepositoryCountBatchTest extends TestCase {
	private $previous_wpdb;
	private $db;

	protected function setUp(): void {
		$this->previous_wpdb = $GLOBALS['wpdb'] ?? null;
		$this->db = new Alynt_Drime_Backups_Dashboard_Test_Count_Batch_WPDB();
		$GLOBALS['wpdb'] = $this->db;
	}
	protected function tearDown(): void { $GLOBALS['wpdb'] = $this->previous_wpdb; }

	private function read( $kind, array $ids ) {
		return $kind === 'snapshots' ? ( new Alynt_Drime_Backups_Dashboard_Snapshot_Repository() )->counts_by_site_ids( $ids ) : ( new Alynt_Drime_Backups_Dashboard_Remote_Action_Repository() )->counts_by_site_ids( $ids, $kind === 'non_terminal' );
	}

	public function kinds() { return array( array( 'snapshots' ), array( 'actions' ), array( 'non_terminal' ) ); }

	/** @dataProvider kinds */
	public function test_scoped_grouped_counts_are_not_history_limited_and_fill_empty_keys( $kind ) {
		$this->db->responses = array( array( array( 'dashboard_site_id' => '2', 'row_count' => '20000' ), array( 'dashboard_site_id' => '999', 'row_count' => '888' ) ) );
		$result = $this->read( $kind, array( 2, '2', 7 ) );
		$this->assertSame( array( 2 => 20000, 7 => 0 ), $result );
		$this->assertSame( array( 2, 7 ), $this->db->prepared[0]['args'] );
		$this->assertCount( 1, $this->db->queries );
		$this->assertSame( array(), $this->db->fallback_queries );
		$sql = $this->db->queries[0];
		$this->assertStringContainsString( 'SELECT dashboard_site_id, COUNT(*) AS row_count', $sql );
		$this->assertStringContainsString( $kind === 'snapshots' ? 'wp_alynt_drime_dashboard_snapshots' : 'wp_alynt_drime_dashboard_actions', $sql );
		$this->assertStringContainsString( 'WHERE dashboard_site_id IN (%d,%d)', $sql );
		$this->assertStringContainsString( 'GROUP BY dashboard_site_id', $sql );
		$this->assertStringNotContainsString( 'LIMIT', $sql );
		$this->assertStringNotContainsString( 'payload', $sql );
		$this->assertStringNotContainsString( 'SUM(', $sql );
		if ( $kind === 'non_terminal' ) {
			$this->assertStringContainsString( "AND state IN ('queued_for_dispatch', 'accepted', 'running')", $sql );
		} else { $this->assertStringNotContainsString( 'state IN', $sql ); }
	}

	/** @dataProvider kinds */
	public function test_fifty_site_statement_budget_and_no_empty_input_reads( $kind ) {
		$this->assertSame( array(), $this->read( $kind, array() ) );
		$this->assertSame( array(), $this->db->prepared );
		foreach ( array( 1 => 1, 50 => 1, 51 => 2, 100 => 2, 500 => 10 ) as $size => $expected ) {
			$this->db->queries = $this->db->prepared = array();
			$this->db->responses = array_fill( 0, $expected, array() );
			$this->assertSame( array_fill_keys( range( 1, $size ), 0 ), $this->read( $kind, range( 1, $size ) ) );
			$this->assertCount( $expected, $this->db->queries );
			foreach ( $this->db->prepared as $statement ) {
				$this->assertLessThanOrEqual( 50, count( $statement['args'] ) );
				$this->assertSame( count( $statement['args'] ), substr_count( $statement['sql'], '%d' ) );
			}
		}
		$this->assertSame( array(), $this->db->fallback_queries );
	}

	/** @dataProvider kinds */
	public function test_failed_batch_uses_legacy_scalar_readers_then_continues( $kind ) {
		$this->db->responses = array( false, array( array( 'dashboard_site_id' => '51', 'row_count' => '80' ) ) );
		$this->db->fallback_values = range( 1, 50 );
		$result = $this->read( $kind, range( 1, 51 ) );
		$this->assertCount( 2, $this->db->queries );
		$this->assertCount( 50, $this->db->fallback_queries );
		$this->assertSame( array_combine( range( 1, 51 ), array_merge( range( 1, 50 ), array( 80 ) ) ), $result );
		foreach ( $this->db->fallback_queries as $sql ) {
			$this->assertStringContainsString( 'SELECT COUNT(*) FROM ', $sql );
			$this->assertStringContainsString( 'WHERE dashboard_site_id = %d', $sql );
			if ( $kind === 'non_terminal' ) { $this->assertStringContainsString( "state IN ('queued_for_dispatch', 'accepted', 'running')", $sql ); }
		}
	}

	/** @dataProvider kinds */
	public function test_empty_array_with_database_error_uses_scalar_fallback_but_valid_empty_does_not( $kind ) {
		$this->db->responses = array( array(), array() );
		$this->db->response_errors = array( 'owned grouped failure', '' );
		$this->db->fallback_values = array( '8' );
		$this->assertSame( array( 1 => 8 ), $this->read( $kind, array( 1 ) ) );
		$this->assertSame( array( 2 => 0 ), $this->read( $kind, array( 2 ) ) );
		$this->assertCount( 1, $this->db->fallback_queries );
		$this->assertCount( 2, $this->db->queries );
	}

	/** @dataProvider kinds */
	public function test_total_failure_keeps_zero_convention_without_retrying_twice( $kind ) {
		$this->db->responses = array( null );
		$this->db->fallback_values = array( null, false );
		$this->assertSame( array( 1 => 0, 2 => 0 ), $this->read( $kind, array( 1, 2 ) ) );
		$this->assertCount( 1, $this->db->queries );
		$this->assertCount( 2, $this->db->fallback_queries );
	}

	public function test_snapshot_signed_zero_and_action_absolute_id_policies_match_single_readers() {
		$this->db->responses = array( array(), array(), array() );
		$this->assertSame( array( -2 => 0, 0 => 0, 2 => 0 ), $this->read( 'snapshots', array( -2, 0, 2 ) ) );
		$this->assertSame( array( -2, 0, 2 ), $this->db->prepared[0]['args'] );
		$this->assertSame( array( 2 => 0 ), $this->read( 'actions', array( -2, 0, 2 ) ) );
		$this->assertSame( array( 2 ), $this->db->prepared[1]['args'] );
		$this->assertSame( array(), $this->read( 'non_terminal', array( 0 ) ) );
		$this->assertCount( 2, $this->db->queries );
	}

	public function test_empty_requests_need_no_database_collaborator() {
		$GLOBALS['wpdb'] = null;
		$this->assertSame( array(), $this->read( 'snapshots', array() ) );
		$this->assertSame( array(), $this->read( 'actions', array() ) );
		$this->assertSame( array(), $this->read( 'non_terminal', array( 0 ) ) );
	}
}
