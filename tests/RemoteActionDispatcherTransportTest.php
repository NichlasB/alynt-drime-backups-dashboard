<?php
/**
 * Remote action dispatcher transport safety tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/remote-action-dispatcher-test-bootstrap.php';

/**
 * Tests remote action dispatcher safe transport decisions.
 */
class RemoteActionDispatcherTransportTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_Test_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_WPDB_Setup;

	public function test_same_origin_self_action_allows_private_resolution_without_unsafe_url_rejection() {
		$this->wpdb->site = $this->site_row(
			array(
				'expected_origin' => 'https://control.sitesmanage.com',
			)
		);
		$captured         = array();
		$http             = function ( $url, $args ) use ( &$captured ) {
			$captured = array(
				'url'  => $url,
				'args' => $args,
			);
			$body     = json_decode( $args['body'], true );

			return array(
				'response' => array(
					'code' => 202,
				),
				'body'     => wp_json_encode(
					array(
						'protocol_version' => 2,
						'action_id'        => $body['action_id'],
						'state'            => 'accepted',
						'code'             => 'action_accepted',
						'summary'          => 'Accepted safely.',
						'retry_after'      => 0,
					)
				),
			);
		};

		$result = $this->dispatcher(
			$http,
			function () {
				return array( '127.0.0.1' );
			}
		)->request_scan_upload_now( 9, 7 );

		$this->assertIsArray( $result );
		$this->assertSame( 'accepted', $result['remote_state'] );
		$this->assertSame( 'https://control.sitesmanage.com/wp-json/alynt-drime-backups-uploader/v2/action-intents', $captured['url'] );
		$this->assertFalse( $captured['args']['reject_unsafe_urls'] );
	}

	public function test_non_same_origin_action_still_rejects_private_resolution() {
		$called = false;
		$http   = function () use ( &$called ) {
			$called = true;
			return array();
		};

		$result = $this->dispatcher(
			$http,
			function () {
				return array( '127.0.0.1' );
			}
		)->request_scan_upload_now( 9, 7 );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'remote_action_destination_unsafe', $result->get_error_code() );
		$this->assertFalse( $called );
		$this->assertSame( array(), $this->wpdb->inserted_data );
	}
}
