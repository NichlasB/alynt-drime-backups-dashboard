<?php
/**
 * Safe transport tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/safe-transport-test-harness.php';

/**
 * Tests status request preparation.
 */
class SafeTransportTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Safe_Transport_Test_Fixtures;

	/**
	 * Transport prepares fixed GET status request without executing HTTP.
	 *
	 * @return void
	 */
	public function test_prepare_status_request_builds_fixed_read_only_get() {
		$transport = $this->transport();
		$request   = $transport->prepare_status_request(
			$this->status_site( 'https://Client.Example.com/' ),
			$this->polling_authorization()
		);

		$this->assertIsArray( $request );
		$this->assertSame( 'https://client.example.com/wp-json/alynt-drime-backups-uploader/v1/status', $request['url'] );
		$this->assertSame( 'GET', $request['args']['method'] );
		$this->assertSame( 0, $request['args']['redirection'] );
		$this->assertSame( Alynt_Drime_Backups_Dashboard_Safe_Transport::MAX_RESPONSE_SIZE_BYTES, $request['args']['limit_response_size'] );
		$this->assertTrue( $request['args']['reject_unsafe_urls'] );
		$this->assertSame( 'application/json', $request['args']['headers']['Accept'] );
		$this->assertSame( 'no-store', $request['args']['headers']['Cache-Control'] );
		$this->assertArrayHasKey( 'Authorization', $request['args']['headers'] );
	}

	/**
	 * Same-origin dashboard self-polling may use loopback host resolution.
	 *
	 * @return void
	 */
	public function test_prepare_status_request_allows_same_origin_self_poll_with_loopback_resolution() {
		$transport = $this->transport(
			function () {
				return array( '127.0.0.1' );
			}
		);
		$request   = $transport->prepare_status_request(
			$this->status_site( 'https://control.sitesmanage.com' ),
			$this->polling_authorization()
		);

		$this->assertIsArray( $request );
		$this->assertSame( 'https://control.sitesmanage.com/wp-json/alynt-drime-backups-uploader/v1/status', $request['url'] );
		$this->assertFalse( $request['args']['reject_unsafe_urls'] );
	}

}
