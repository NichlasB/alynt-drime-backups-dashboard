<?php
/**
 * Poller failure-path tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/poller-test-harness.php';
require_once __DIR__ . '/support/poller-test-fixtures.php';

/**
 * Tests manual status check failure handling.
 */
class PollerFailureTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Test_Poller_Fixtures;

	/**
	 * Invalid payload marks safe failure without recording a snapshot.
	 *
	 * @return void
	 */
	public function test_invalid_payload_marks_failure_without_snapshot() {
		$vault      = new Alynt_Drime_Backups_Dashboard_Credential_Vault( str_repeat( 'k', 64 ) );
		$sites      = new Alynt_Drime_Backups_Dashboard_Test_Poller_Site_Repository( $this->site( $vault ) );
		$snapshots  = new Alynt_Drime_Backups_Dashboard_Test_Poller_Snapshot_Repository();
		$http_client = function () {
			return array(
				'response' => array(
					'code' => 200,
				),
				'body'     => wp_json_encode(
					array_merge(
						$this->payload(),
						array(
							'site_uuid' => '22222222-2222-4222-8222-222222222222',
						)
					)
				),
			);
		};
		$poller     = $this->poller( $sites, $snapshots, $vault, $http_client );

		$result = $poller->check_status_now( 77 );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'site_uuid_mismatch', $result->get_error_code() );
		$this->assertSame( array(), $snapshots->recorded );
		$this->assertSame( 'site_uuid_mismatch', $sites->failure['error_code'] );
		$this->assertSame( 1, $sites->failure['consecutive_failures'] );
		$this->assertNotEmpty( $sites->failure['next_poll_at'] );
		$this->assertSame( array(), $sites->success );
	}

	/**
	 * Missing credentials fail before transport.
	 *
	 * @return void
	 */
	public function test_missing_polling_credentials_fail_before_transport() {
		$sites      = new Alynt_Drime_Backups_Dashboard_Test_Poller_Site_Repository(
			array(
				'id'              => 77,
				'public_id'       => '00000000-0000-4000-8000-000000000000',
				'expected_origin' => 'https://client.example.com',
				'site_uuid'       => '11111111-1111-4111-8111-111111111111',
			)
		);
		$snapshots  = new Alynt_Drime_Backups_Dashboard_Test_Poller_Snapshot_Repository();
		$http_client = function () {
			$this->fail( 'HTTP client should not be called without credentials.' );
		};
		$poller     = $this->poller( $sites, $snapshots, new Alynt_Drime_Backups_Dashboard_Credential_Vault( str_repeat( 'k', 64 ) ), $http_client );

		$result = $poller->check_status_now( 77 );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'auth_missing', $result->get_error_code() );
		$this->assertSame( array(), $snapshots->recorded );
		$this->assertSame( 'auth_missing', $sites->failure['error_code'] );
	}

	/**
	 * Failure backoff increments the stored failure counter.
	 *
	 * @return void
	 */
	public function test_failure_backoff_increments_consecutive_failures() {
		$vault = new Alynt_Drime_Backups_Dashboard_Credential_Vault( str_repeat( 'k', 64 ) );
		$sites = new Alynt_Drime_Backups_Dashboard_Test_Poller_Site_Repository(
			$this->site(
				$vault,
				array(
					'consecutive_failures' => 2,
				)
			)
		);
		$snapshots = new Alynt_Drime_Backups_Dashboard_Test_Poller_Snapshot_Repository();

		$http_client = function () {
			return new WP_Error( 'transport_failed', 'Client status endpoint unavailable.' );
		};
		$poller      = $this->poller( $sites, $snapshots, $vault, $http_client );

		$result = $poller->check_status_now( 77 );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'transport_failed', $sites->failure['error_code'] );
		$this->assertSame( 3, $sites->failure['consecutive_failures'] );
		$this->assertNotEmpty( $sites->failure['next_poll_at'] );
		$this->assertSame( array(), $snapshots->recorded );
	}

	/**
	 * Snapshot storage failures are surfaced instead of reporting success.
	 *
	 * @return void
	 */
	public function test_snapshot_storage_failure_stops_success_notice() {
		$vault                    = new Alynt_Drime_Backups_Dashboard_Credential_Vault( str_repeat( 'k', 64 ) );
		$sites                    = new Alynt_Drime_Backups_Dashboard_Test_Poller_Site_Repository( $this->site( $vault ) );
		$snapshots                = new Alynt_Drime_Backups_Dashboard_Test_Poller_Snapshot_Repository();
		$snapshots->record_result = new WP_Error( 'snapshot_store_failed', 'Snapshot could not be stored.' );
		$http_client              = function () {
			return array(
				'response' => array(
					'code' => 200,
				),
				'body'     => wp_json_encode( $this->payload() ),
			);
		};
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
		$vault                      = new Alynt_Drime_Backups_Dashboard_Credential_Vault( str_repeat( 'k', 64 ) );
		$sites                      = new Alynt_Drime_Backups_Dashboard_Test_Poller_Site_Repository( $this->site( $vault ) );
		$sites->mark_success_result = false;
		$snapshots                  = new Alynt_Drime_Backups_Dashboard_Test_Poller_Snapshot_Repository();
		$http_client                = function () {
			return array(
				'response' => array(
					'code' => 200,
				),
				'body'     => wp_json_encode( $this->payload() ),
			);
		};
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
		$vault                      = new Alynt_Drime_Backups_Dashboard_Credential_Vault( str_repeat( 'k', 64 ) );
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
