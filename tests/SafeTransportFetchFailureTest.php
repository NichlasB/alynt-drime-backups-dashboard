<?php
/**
 * Safe transport fetch failure tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/safe-transport-test-harness.php';

/**
 * Tests status fetch response failure handling.
 */
class SafeTransportFetchFailureTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Safe_Transport_Test_Fixtures;

	/**
	 * Non-JSON responses fail safely.
	 *
	 * @return void
	 */
	public function test_fetch_status_payload_rejects_non_json_response() {
		$transport = $this->transport();
		$result    = $transport->fetch_status_payload(
			$this->status_site(),
			$this->polling_authorization(),
			function () {
				return $this->http_response( 200, 'not json' );
			}
		);

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'json_invalid', $result->get_error_code() );
	}

	/**
	 * Transport timeout errors get timeout-specific messaging.
	 *
	 * @return void
	 */
	public function test_fetch_status_payload_returns_timeout_error() {
		$transport = $this->transport();
		$result    = $transport->fetch_status_payload(
			$this->status_site(),
			$this->polling_authorization(),
			function () {
				return new WP_Error( 'http_request_failed', 'cURL error 28: Operation timed out after 10000 milliseconds.' );
			}
		);

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'transport_timeout', $result->get_error_code() );
	}

	/**
	 * Non-200 HTTP responses include their status.
	 *
	 * @return void
	 */
	public function test_fetch_status_payload_returns_http_status_error() {
		$transport = $this->transport();
		$result    = $transport->fetch_status_payload(
			$this->status_site(),
			$this->polling_authorization(),
			function () {
				return $this->http_response( 503, '' );
			}
		);

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'transport_http_status', $result->get_error_code() );
		$this->assertSame( 503, $result->get_error_data()['status'] );
	}

	/**
	 * Oversized JSON responses fail before decode.
	 *
	 * @return void
	 */
	public function test_fetch_status_payload_rejects_oversized_response_body() {
		$transport = $this->transport();
		$result    = $transport->fetch_status_payload(
			$this->status_site(),
			$this->polling_authorization(),
			function () {
				return $this->http_response( 200, str_repeat( ' ', Alynt_Drime_Backups_Dashboard_Safe_Transport::MAX_RESPONSE_SIZE_BYTES + 1 ) );
			}
		);

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'response_too_large', $result->get_error_code() );
	}
}
