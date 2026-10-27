<?php
/** Actual Diagnostics collection with in-memory count repositories only. */
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/support/diagnostics-test-bootstrap.php';
require_once __DIR__ . '/support/diagnostics-local-removal-fixtures.php';
require_once __DIR__ . '/support/archived-counting-repositories.php';

class DiagnosticsArchivedCountBatchTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Test_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Local_Removal_Fixtures;

	private function diagnostics( array $sites, $snapshots, $actions ) {
		return new Alynt_Drime_Backups_Dashboard_Diagnostics( new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Site_Repository( $sites ), $snapshots, null, null, $actions );
	}

	public function test_one_hundred_archived_records_use_three_bulk_requests_and_positive_exact_counts() {
		$sites = $histories = array();
		foreach ( range( 1, 100 ) as $id ) {
			$sites[] = $this->archived_revoked_site( $id, '2026-10-01 00:00:00' );
			$histories[ $id ] = array( array(), array() );
		}
		$sites[] = $this->site( 101 );
		$histories[101] = array_fill( 0, 1000, array() );
		$snapshots = new Alynt_Drime_Backups_Dashboard_Test_Archived_Snapshots( array(), $histories );
		$actions = new Alynt_Drime_Backups_Dashboard_Test_Archived_Actions( array_fill_keys( range( 1, 101 ), 4 ), array_fill_keys( range( 1, 10 ), 1 ) );
		$result = $this->diagnostics( $sites, $snapshots, $actions )->collect();
		$this->assertSame( array( range( 1, 100 ) ), $snapshots->requests );
		$this->assertSame( array( array( range( 1, 100 ), false ), array( range( 1, 100 ), true ) ), $actions->requests );
		$this->assertSame( array( 'archived_records' => 100, 'ready_records' => 90, 'blocked_records' => 10, 'retained_snapshot_rows' => 200, 'retained_action_rows' => 400, 'non_terminal_action_rows' => 10 ), $result['counts']['local_removal'] );
		$this->assertStringNotContainsString( 'client1.example.com', wp_json_encode( $result['support'] ) );
	}

	public function test_no_archived_records_never_request_count_maps() {
		$snapshots = new Alynt_Drime_Backups_Dashboard_Test_Archived_Snapshots( array() );
		$actions = new Alynt_Drime_Backups_Dashboard_Test_Archived_Actions();
		foreach ( array( array(), array( $this->site( 1 ) ) ) as $sites ) {
			$result = $this->diagnostics( $sites, $snapshots, $actions )->collect();
			$this->assertSame( array(), $snapshots->requests );
			$this->assertSame( array(), $actions->requests );
			$this->assertSame( 0, $result['counts']['local_removal']['archived_records'] );
		}
	}

	public function test_existing_readiness_reasons_and_aggregate_outputs_are_unchanged() {
		$cases = array(
			array( array(), 0, '' ),
			array( array( 'enrollment_status' => 'pending', 'pairing_expires_at' => '2020-01-01 00:00:00' ), 0, '' ),
			array( array( 'enrollment_status' => 'active' ), 0, 'not_terminal_status' ),
			array( array( 'enrollment_status' => 'pending', 'pairing_expires_at' => '2099-01-01 00:00:00' ), 0, 'not_terminal_status' ),
			array( array( 'polling_key_id' => 'owned-key' ), 0, '' ),
			array( array( 'polling_secret_ciphertext' => 'owned-cipher' ), 0, '' ),
			array( array( 'polling_key_id' => 'owned-key', 'polling_secret_ciphertext' => 'owned-cipher' ), 0, 'polling_credentials_present' ),
			array( array( 'polling_key_id' => 'owned-key', 'has_polling_secret' => 1 ), 0, 'polling_credentials_present' ),
			array( array( 'action_key_id' => 'owned-key' ), 0, 'action_credentials_present' ),
			array( array( 'action_private_key_ciphertext' => 'owned-cipher' ), 0, 'action_credentials_present' ),
			array( array( 'next_poll_at' => '2099-01-01 00:00:00' ), 0, 'scheduled_poll_present' ),
			array( array( 'paused_at' => '2026-10-01 00:00:00' ), 0, 'paused_not_terminal' ),
			array( array(), 1, 'non_terminal_action_history' ),
		);
		$method = new ReflectionMethod( Alynt_Drime_Backups_Dashboard_Diagnostics::class, 'local_removal_readiness_blocking_reason' );
		if ( PHP_VERSION_ID < 80100 ) { $method->setAccessible( true ); }
		foreach ( $cases as list( $overrides, $non_terminal, $reason ) ) {
			$site = array_merge( $this->archived_revoked_site( 1, '2026-10-01 00:00:00' ), $overrides );
			$snapshots = new Alynt_Drime_Backups_Dashboard_Test_Archived_Snapshots( array(), array( 1 => array_fill( 0, 8, array() ) ) );
			$actions = new Alynt_Drime_Backups_Dashboard_Test_Archived_Actions( array( 1 => 5 ), array( 1 => $non_terminal ) );
			$diagnostics = $this->diagnostics( array( $site ), $snapshots, $actions );
			$result = $diagnostics->collect()['counts']['local_removal'];
			$this->assertSame( $reason, $method->invoke( $diagnostics, $site, $non_terminal ) );
			$this->assertSame( array( 'archived_records' => 1, 'ready_records' => $reason === '' ? 1 : 0, 'blocked_records' => $reason === '' ? 0 : 1, 'retained_snapshot_rows' => 8, 'retained_action_rows' => 5, 'non_terminal_action_rows' => $non_terminal ), $result );
		}
	}

	public function test_signed_and_zero_snapshot_counts_use_original_ids_but_actions_use_absolute_ids() {
		$sites = array( $this->archived_revoked_site( -2, '2026-10-01 00:00:00' ), $this->archived_revoked_site( 0, '2026-10-01 00:00:00' ) );
		$snapshots = new Alynt_Drime_Backups_Dashboard_Test_Archived_Snapshots( array(), array( -2 => array( array() ), 0 => array( array(), array() ) ) );
		$actions = new Alynt_Drime_Backups_Dashboard_Test_Archived_Actions( array( 2 => 7 ), array( 2 => 1 ) );
		$result = $this->diagnostics( $sites, $snapshots, $actions )->collect()['counts']['local_removal'];
		$this->assertSame( 3, $result['retained_snapshot_rows'] );
		$this->assertSame( 7, $result['retained_action_rows'] );
		$this->assertSame( 1, $result['non_terminal_action_rows'] );
		$this->assertSame( 1, $result['blocked_records'] );
	}
}
