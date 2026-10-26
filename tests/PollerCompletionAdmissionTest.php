<?php
/**
 * Real poller/repository flow with modeled SQL and no HTTP or database sockets.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/poll-completion-fixtures.php';
require_once __DIR__ . '/support/poller-test-fixtures.php';

class PollerCompletionAdmissionTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Test_Poller_Fixtures;

	/** @dataProvider results */
	public function test_real_completion_after_transport_interleaving( $failure, $revoke ) {
		$previous = $GLOBALS['wpdb'] ?? null;
		$database = new Alynt_Drime_Backups_Dashboard_Test_Poll_Completion_WPDB();
		$vault = $this->vault();
		$database->row = $this->site( $vault );
		$GLOBALS['wpdb'] = $database;
		$sites = new class() extends Alynt_Drime_Backups_Dashboard_Site_Repository {
			public function get( $site_id ) {
				return $GLOBALS['wpdb']->row;
			}
		};
		$snapshots = new Alynt_Drime_Backups_Dashboard_Test_Poller_Snapshot_Repository();
		$reconciler = new Alynt_Drime_Backups_Dashboard_Test_Poller_Remote_Action_Reconciler();
		$response = $this->successful_http_client();
		$http = function () use ( $sites, $failure, $revoke, $response ) {
			if ( $revoke ) {
				$this->assertTrue( $sites->revoke_local( 77 ) );
			}
			return $failure ? new WP_Error( 'transport_failed', 'Unavailable' ) : $response();
		};
		try {
			$result = $this->poller( $sites, $snapshots, $vault, $http, $reconciler )->check_status_now( 77 );
			if ( $revoke || $failure ) {
				$this->assertInstanceOf( WP_Error::class, $result );
				$this->assertSame( $revoke ? 'poll_completion_stale' : 'transport_failed', $result->get_error_code() );
				$this->assertSame( array(), $reconciler->calls );
			} else {
				$this->assertSame( 'working', $result['category'] );
				$this->assertCount( 1, $reconciler->calls );
			}
			$this->assertSame( $revoke ? 'revoked' : ( $failure ? 'awaiting_first_poll' : 'active' ), $database->row['enrollment_status'] );
			if ( $revoke ) {
				$this->assertNull( $database->row['polling_secret_ciphertext'] );
			}
			// Snapshot-first is preserved; this foundation is not an atomic multi-write transaction.
			$this->assertSame( $failure ? array() : 'working', $failure ? $snapshots->recorded : $snapshots->recorded['status'] );
		} finally {
			$GLOBALS['wpdb'] = $previous;
		}
	}

	public function test_snapshot_failure_preserves_stale_completion_error() {
		$vault = $this->vault();
		$site = $this->site( $vault );
		$sites = new Alynt_Drime_Backups_Dashboard_Test_Poller_Site_Repository( $site );
		$sites->mark_failure_result = new WP_Error( 'poll_completion_stale', 'Changed' );
		$snapshots = new Alynt_Drime_Backups_Dashboard_Test_Poller_Snapshot_Repository();
		$snapshots->record_result = new WP_Error( 'snapshot_store_failed', 'Unavailable' );
		$result = $this->poller( $sites, $snapshots, $vault, $this->successful_http_client() )->check_status_now( 77 );
		$this->assertSame( 'poll_completion_stale', $result->get_error_code() );
		$this->assertSame( $site, $sites->failure['expected_site'] );
		$this->assertSame( array(), $sites->success );
	}

	public static function results(): array {
		return array( array( false, false ), array( true, false ), array( false, true ), array( true, true ) );
	}
}
