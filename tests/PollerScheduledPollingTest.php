<?php
/**
 * Scheduled poller tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/poller-test-harness.php';
require_once __DIR__ . '/support/poller-test-fixtures.php';

/**
 * Tests bounded scheduled status polling.
 */
class PollerScheduledPollingTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Test_Poller_Fixtures;

	/**
	 * Scheduled polling processes only the bounded due-site batch.
	 *
	 * @return void
	 */
	public function test_scheduled_poll_processes_bounded_due_site_batch() {
		$vault = new Alynt_Drime_Backups_Dashboard_Credential_Vault( str_repeat( 'k', 64 ) );
		$sites = new Alynt_Drime_Backups_Dashboard_Test_Poller_Site_Repository(
			array(
				$this->site( $vault, array( 'id' => 77 ) ),
				$this->site( $vault, array( 'id' => 78 ) ),
				$this->site( $vault, array( 'id' => 79 ) ),
			)
		);
		$snapshots = new Alynt_Drime_Backups_Dashboard_Test_Poller_Snapshot_Repository();
		$calls     = 0;

		$http_client = function () use ( &$calls ) {
			++$calls;

			return array(
				'response' => array(
					'code' => 200,
				),
				'body'     => wp_json_encode( $this->payload() ),
			);
		};
		$poller      = $this->poller( $sites, $snapshots, $vault, $http_client );

		$result = $poller->poll_sites( 2 );

		$this->assertSame( 2, $sites->due_query['limit'] );
		$this->assertSame( 2, $calls );
		$this->assertSame( 2, $result['processed'] );
		$this->assertSame( 2, $result['success'] );
		$this->assertSame( 0, $result['failure'] );
		$this->assertCount( 2, $sites->successes );
	}

	/**
	 * Scheduled polling uses the tuned default batch size when no override is supplied.
	 *
	 * @return void
	 */
	public function test_scheduled_poll_uses_default_batch_size_without_override() {
		$vault = new Alynt_Drime_Backups_Dashboard_Credential_Vault( str_repeat( 'k', 64 ) );
		$sites = new Alynt_Drime_Backups_Dashboard_Test_Poller_Site_Repository(
			array(
				$this->site( $vault, array( 'id' => 77 ) ),
			)
		);
		$snapshots = new Alynt_Drime_Backups_Dashboard_Test_Poller_Snapshot_Repository();

		$http_client = function () {
			return array(
				'response' => array(
					'code' => 200,
				),
				'body'     => wp_json_encode( $this->payload() ),
			);
		};
		$poller      = $this->poller( $sites, $snapshots, $vault, $http_client );

		$result = $poller->poll_sites();

		$this->assertSame( Alynt_Drime_Backups_Dashboard_Poller::DEFAULT_BATCH_SIZE, $sites->due_query['limit'] );
		$this->assertSame( 20, $sites->due_query['limit'] );
		$this->assertSame( 1, $result['processed'] );
		$this->assertSame( 1, $result['success'] );
	}
}
