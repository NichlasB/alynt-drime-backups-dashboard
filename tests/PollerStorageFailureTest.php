<?php
/**
 * Poller storage failure-path tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/poller-test-harness.php';
require_once __DIR__ . '/support/poller-test-fixtures.php';

/**
 * Tests poller persistence failure handling.
 */
class PollerStorageFailureTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Test_Poller_Fixtures;

	/**
	 * Snapshot storage failures are surfaced instead of reporting success.
	 *
	 * @return void
	 */
	public function test_snapshot_storage_failure_stops_success_notice() {
		$vault                    = $this->vault();
		$sites                    = new Alynt_Drime_Backups_Dashboard_Test_Poller_Site_Repository( $this->site( $vault ) );
		$snapshots                = new Alynt_Drime_Backups_Dashboard_Test_Poller_Snapshot_Repository();
		$snapshots->record_result = new WP_Error( 'snapshot_store_failed', 'Snapshot could not be stored.' );
		$http_client              = $this->successful_http_client();
		$poller                   = $this->poller( $sites, $snapshots, $vault, $http_client );

		$result = $poller->check_status_now( 77 );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'snapshot_store_failed', $result->get_error_code() );
		$this->assertSame( array(), $sites->success );
		$this->assertSame( 'snapshot_store_failed', $sites->failure['error_code'] );
	}

	/**
	 * Poll success storage failures are surfaced.
	 *
	 * @return void
	 */
	public function test_poll_success_storage_failure_is_returned() {
		$vault                      = $this->vault();
		$sites                      = new Alynt_Drime_Backups_Dashboard_Test_Poller_Site_Repository( $this->site( $vault ) );
		$sites->mark_success_result = false;
		$snapshots                  = new Alynt_Drime_Backups_Dashboard_Test_Poller_Snapshot_Repository();
		$http_client                = $this->successful_http_client();
		$poller                     = $this->poller( $sites, $snapshots, $vault, $http_client );

		$result = $poller->check_status_now( 77 );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'poll_success_store_failed', $result->get_error_code() );
		$this->assertSame( 'working', $snapshots->recorded['status'] );
	}

	/**
	 * Failure persistence failures are surfaced.
	 *
	 * @return void
	 */
	public function test_poll_failure_storage_failure_is_returned() {
		$vault                      = $this->vault();
		$sites                      = new Alynt_Drime_Backups_Dashboard_Test_Poller_Site_Repository( $this->site( $vault ) );
		$sites->mark_failure_result = false;
		$snapshots                  = new Alynt_Drime_Backups_Dashboard_Test_Poller_Snapshot_Repository();
		$http_client                = function () {
			return new WP_Error( 'transport_failed', 'Client status endpoint unavailable.' );
		};
		$poller                     = $this->poller( $sites, $snapshots, $vault, $http_client );

		$result = $poller->check_status_now( 77 );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'poll_failure_store_failed', $result->get_error_code() );
		$this->assertSame( 'transport_failed', $result->get_error_data()['original_error_code'] );
	}
}
