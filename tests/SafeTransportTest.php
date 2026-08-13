<?php
/**
 * Safe transport tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/includes/class-origin-validator.php';
require_once dirname( __DIR__ ) . '/includes/class-safe-transport.php';

/**
 * Tests status request preparation.
 */
class SafeTransportTest extends TestCase {
	/**
	 * Transport prepares fixed GET status request without executing HTTP.
	 *
	 * @return void
	 */
	public function test_prepare_status_request_builds_fixed_read_only_get() {
		$transport = $this->transport();
		$request   = $transport->prepare_status_request(
			array(
				'expected_origin' => 'https://Client.Example.com/',
			),
			'Bearer adb-poll-v1.pk_example_0000000000000000.' . str_repeat( 'A', 43 )
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
	 * Unsafe origins fail before request construction.
	 *
	 * @return void
	 */
	public function test_prepare_status_request_rejects_unsafe_destination() {
		$transport = $this->transport();
		$result    = $transport->prepare_status_request(
			array(
				'expected_origin' => 'http://127.0.0.1',
			),
			'Bearer adb-poll-v1.pk_example_0000000000000000.' . str_repeat( 'A', 43 )
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
			array(
				'expected_origin' => 'https://client.example.com',
			),
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
			array(
				'expected_origin' => 'https://client.example.com',
			),
			'Bearer adb-poll-v1.pk_example_0000000000000000.' . str_repeat( 'A', 43 )
		);

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'destination_unsafe', $result->get_error_code() );
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
			array(
				'expected_origin' => 'https://control.sitesmanage.com',
			),
			'Bearer adb-poll-v1.pk_example_0000000000000000.' . str_repeat( 'A', 43 )
		);

		$this->assertIsArray( $request );
		$this->assertSame( 'https://control.sitesmanage.com/wp-json/alynt-drime-backups-uploader/v1/status', $request['url'] );
		$this->assertFalse( $request['args']['reject_unsafe_urls'] );
	}

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

	/**
	 * Creates a transport with a public resolver by default.
	 *
	 * @param callable|null $resolver Resolver.
	 * @return Alynt_Drime_Backups_Dashboard_Safe_Transport
	 */
	private function transport( $resolver = null ) {
		if ( null === $resolver ) {
			$resolver = function () {
				return array( '93.184.216.34' );
			};
		}

		return new Alynt_Drime_Backups_Dashboard_Safe_Transport( new Alynt_Drime_Backups_Dashboard_Origin_Validator(), $resolver );
	}
}
