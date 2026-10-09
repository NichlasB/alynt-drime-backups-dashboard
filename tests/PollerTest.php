<?php
/**
 * Poller tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/poller-test-harness.php';
require_once __DIR__ . '/support/poller-test-fixtures.php';

/**
 * Tests manual status checks.
 */
class PollerTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Test_Poller_Fixtures;

	/**
	 * Successful manual poll records snapshot and activates the site.
	 *
	 * @return void
	 */
	public function test_check_status_now_records_snapshot_and_marks_success() {
		$vault      = new Alynt_Drime_Backups_Dashboard_Credential_Vault( str_repeat( 'k', 64 ) );
		$site       = $this->site( $vault );
		$sites      = new Alynt_Drime_Backups_Dashboard_Test_Poller_Site_Repository( $site );
		$snapshots  = new Alynt_Drime_Backups_Dashboard_Test_Poller_Snapshot_Repository();
		$captured   = array();
		$http_client = $this->successful_capturing_http_client( $captured );
		$poller     = $this->poller( $sites, $snapshots, $vault, $http_client );

		$result = $poller->check_status_now( 77 );

		$this->assertIsArray( $result );
		$this->assertSame( 'working', $result['category'] );
		$this->assertSame( 555, $result['snapshot_id'] );
		$this->assertStringStartsWith( 'https://client.example.com/wp-json/alynt-drime-backups-uploader/v1/status?', $captured['url'] );
		$this->assertStringContainsString( '_adbd_cache_bust=', $captured['url'] );
		$this->assertSame( 'GET', $captured['args']['method'] );
		$this->assertStringStartsWith( 'Bearer adb-poll-v1.pk_example_', $captured['args']['headers']['Authorization'] );
		$this->assertSame( 'working', $snapshots->recorded['status'] );
		$this->assertSame( 'working', $sites->success['status'] );
		$this->assertSame( '0.5.3', $sites->success['plugin_version'] );
		$this->assertNotEmpty( $sites->success['next_poll_at'] );
		$this->assertSame( array(), $sites->failure );
	}

}
