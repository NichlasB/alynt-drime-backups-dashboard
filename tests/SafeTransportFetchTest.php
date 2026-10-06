<?php
/**
 * Safe transport fetch tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/safe-transport-test-harness.php';

/**
 * Tests status fetch response handling.
 */
class SafeTransportFetchTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Safe_Transport_Test_Fixtures;

	/**
	 * Transport fetches and decodes JSON through an injected HTTP client.
	 *
	 * @return void
	 */
	public function test_fetch_status_payload_uses_injected_http_client() {
		$transport = $this->transport();
		$result    = $transport->fetch_status_payload(
			array(
				'expected_origin' => 'https://client.example.com',
			),
			'Bearer adb-poll-v1.pk_example_0000000000000000.' . str_repeat( 'A', 43 ),
			function ( $url, $args ) {
				$this->assertStringStartsWith( 'https://client.example.com/wp-json/alynt-drime-backups-uploader/v1/status?', $url );
				$this->assertStringContainsString( '_adbd_cache_bust=', $url );
				$this->assertSame( 'GET', $args['method'] );

				return array(
					'response' => array(
						'code' => 200,
					),
					'body'     => '{"schema_version":1}',
				);
			}
		);

		$this->assertSame( array( 'schema_version' => 1 ), $result );
	}

	/**
	 * Non-JSON responses fail safely.
	 *
	 * @return void
	 */
	public function test_fetch_status_payload_rejects_non_json_response() {
		$transport = $this->transport();
		$result    = $transport->fetch_status_payload(
			array(
				'expected_origin' => 'https://client.example.com',
			),
			'Bearer adb-poll-v1.pk_example_0000000000000000.' . str_repeat( 'A', 43 ),
			function () {
				return array(
					'response' => array(
						'code' => 200,
					),
					'body'     => 'not json',
				);
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
			array(
				'expected_origin' => 'https://client.example.com',
			),
			'Bearer adb-poll-v1.pk_example_0000000000000000.' . str_repeat( 'A', 43 ),
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
			array(
				'expected_origin' => 'https://client.example.com',
			),
			'Bearer adb-poll-v1.pk_example_0000000000000000.' . str_repeat( 'A', 43 ),
			function () {
				return array(
					'response' => array(
						'code' => 503,
					),
					'body'     => '',
				);
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
			array(
				'expected_origin' => 'https://client.example.com',
			),
			'Bearer adb-poll-v1.pk_example_0000000000000000.' . str_repeat( 'A', 43 ),
			function () {
				return array(
					'response' => array(
						'code' => 200,
					),
					'body'     => str_repeat( ' ', Alynt_Drime_Backups_Dashboard_Safe_Transport::MAX_RESPONSE_SIZE_BYTES + 1 ),
				);
			}
		);

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'response_too_large', $result->get_error_code() );
	}
}
