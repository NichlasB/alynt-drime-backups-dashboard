<?php
/**
 * Safe transport request rejection tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/safe-transport-test-harness.php';

/**
 * Tests status request preparation rejection handling.
 */
class SafeTransportRequestRejectionTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Safe_Transport_Test_Fixtures;

	/**
	 * Unsafe origins fail before request construction.
	 *
	 * @return void
	 */
	public function test_prepare_status_request_rejects_unsafe_destination() {
		$transport = $this->transport();
		$result    = $transport->prepare_status_request(
			$this->status_site( 'http://127.0.0.1' ),
			$this->polling_authorization()
		);

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'destination_unsafe', $result->get_error_code() );
	}

	/**
	 * Invalid credential shape fails before request construction.
	 *
	 * @return void
	 */
	public function test_prepare_status_request_rejects_invalid_authorization_shape() {
		$transport = $this->transport();
		$result    = $transport->prepare_status_request(
			$this->status_site(),
			'Bearer not-the-v1-scheme'
		);

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'auth_invalid', $result->get_error_code() );
	}

	/**
	 * Private DNS resolution fails before an HTTP request can be built.
	 *
	 * @return void
	 */
	public function test_prepare_status_request_rejects_private_resolved_destination() {
		$transport = $this->transport(
			function () {
				return array( '192.168.1.20' );
			}
		);
		$result    = $transport->prepare_status_request(
			$this->status_site(),
			$this->polling_authorization()
		);

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'destination_unsafe', $result->get_error_code() );
	}
}
