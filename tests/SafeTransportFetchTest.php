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
			$this->status_site(),
			$this->polling_authorization(),
			function ( $url, $args ) {
				$this->assertStringStartsWith( 'https://client.example.com/wp-json/alynt-drime-backups-uploader/v1/status?', $url );
				$this->assertStringContainsString( '_adbd_cache_bust=', $url );
				$this->assertSame( 'GET', $args['method'] );

				return $this->http_response();
			}
		);

		$this->assertSame( array( 'schema_version' => 1 ), $result );
	}

}
