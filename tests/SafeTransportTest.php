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
		$transport = new Alynt_Drime_Backups_Dashboard_Safe_Transport();
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
		$transport = new Alynt_Drime_Backups_Dashboard_Safe_Transport();
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
		$transport = new Alynt_Drime_Backups_Dashboard_Safe_Transport();
		$result    = $transport->prepare_status_request(
			array(
				'expected_origin' => 'https://client.example.com',
			),
			'Bearer not-the-v1-scheme'
		);

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'auth_invalid', $result->get_error_code() );
	}
}
