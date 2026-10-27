<?php
/**
 * Diagnostics batching integration tests with in-memory repositories only.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/diagnostics-test-bootstrap.php';
require_once __DIR__ . '/support/diagnostics-history-batch-repository-double.php';

/**
 * Protects eligibility, positive metrics, query admission and support redaction.
 */
class DiagnosticsHistoryBatchTest extends TestCase {

	use Alynt_Drime_Backups_Dashboard_Diagnostics_Test_Fixtures;

	public function test_one_batch_request_preserves_positive_metrics_for_one_hundred_sites() {
		$sites = array();
		$histories = array();
		for ( $id = 1; $id <= 100; ++$id ) {
			$sites[] = $this->site( $id );
			$histories[ $id ] = array(
				$this->snapshot_row( 'working', '2026-10-27 12:00:00' ),
				$this->snapshot_row( 'needs_attention', '2026-10-27 11:00:00' ),
				$this->snapshot_row( 'working', '2026-10-27 10:00:00' ),
			);
		}
		$repository = new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_History_Batch_Repository( array(), $histories );
		$result = $this->diagnostics( $sites, $repository )->collect();
		$this->assertSame( array( array( range( 1, 100 ), 10 ) ), $repository->batch_requests );
		$this->assertSame( array(
			'records_with_history' => 100,
			'recently_recovered_records' => 100,
			'repeated_attention_records' => 0,
			'recent_attention_transitions' => 100,
		), $result['counts']['attention_history'] );
		$this->assertSame( 'recent_recoveries_seen', $result['summaries']['attention_history'] );
		$this->assertStringNotContainsString( 'client1.example.com', wp_json_encode( $result['support'] ) );
	}

	public function test_archived_invalid_empty_and_sparse_histories_keep_their_existing_meaning() {
		$sites = array( $this->site( 1 ), $this->site( 2 ), $this->site( 3, array( 'archived_at' => '2026-10-27 10:00:00' ) ), $this->site( 0 ), $this->site( 4 ) );
		$history = array(
			$this->snapshot_row( 'working', '2026-10-27 12:00:00' ),
			$this->snapshot_row( 'not_reporting', '2026-10-27 11:00:00' ),
		);
		$repository = new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_History_Batch_Repository( array(), array( 1 => $history, 2 => array( $history[0] ), 3 => $history ) );
		$result = $this->diagnostics( $sites, $repository )->collect();
		$this->assertSame( array( array( array( 1, 2, 4 ), 10 ) ), $repository->batch_requests );
		$this->assertSame( 1, $result['counts']['attention_history']['records_with_history'] );
		$this->assertSame( 1, $result['counts']['attention_history']['recently_recovered_records'] );
	}

	public function test_no_eligible_sites_does_not_request_history() {
		$repository = new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_History_Batch_Repository( array() );
		foreach ( array( array(), array( $this->site( 0 ), $this->site( 3, array( 'archived_at' => '2026-10-27 10:00:00' ) ) ) ) as $sites ) {
			$result = $this->diagnostics( $sites, $repository )->collect();
			$this->assertSame( array(), $repository->batch_requests );
			$this->assertSame( 0, $result['counts']['attention_history']['records_with_history'] );
			$this->assertSame( 'no_retained_history', $result['summaries']['attention_history'] );
		}
	}

	public function test_attention_outside_each_sites_ten_row_window_is_excluded() {
		$working = $this->snapshot_row( 'working', '2026-10-27 12:00:00' );
		$attention = $this->snapshot_row( 'needs_attention', '2026-10-26 12:00:00' );
		$history = array_merge( array_fill( 0, 10, $working ), array( $attention, $working ) );
		$repository = new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_History_Batch_Repository( array(), array( 1 => $history, 2 => array_reverse( $history ) ) );
		$result = $this->diagnostics( array( $this->site( 1 ), $this->site( 2 ) ), $repository )->collect();
		$this->assertSame( array( array( array( 1, 2 ), 10 ) ), $repository->batch_requests );
		$this->assertSame( 2, $result['counts']['attention_history']['records_with_history'] );
		$this->assertSame( 1, $result['counts']['attention_history']['recently_recovered_records'] );
		$this->assertSame( 1, $result['counts']['attention_history']['recent_attention_transitions'] );
	}

	private function diagnostics( array $sites, $snapshots ) {
		return new Alynt_Drime_Backups_Dashboard_Diagnostics(
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Site_Repository( $sites ),
			$snapshots,
			new Alynt_Drime_Backups_Dashboard_Status_Classifier(),
			null,
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Remote_Action_Repository()
		);
	}
}
