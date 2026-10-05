<?php
/**
 * Remote action dispatcher tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/remote-action-dispatcher-test-bootstrap.php';

/**
 * Tests signed remote action dispatch.
 */
class RemoteActionDispatcherTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_Test_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_WPDB_Setup;

	public function test_dispatch_posts_signed_fixed_intent_and_records_acceptance() {
		$captured = array();
		$http     = function ( $url, $args ) use ( &$captured ) {
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

		$result = $this->dispatcher( $http )->request_scan_upload_now( 9, 7 );

		$this->assertIsArray( $result );
		$this->assertSame( 'request_backup_now', $result['action'] );
		$this->assertSame( 'accepted', $result['remote_state'] );
		$this->assertSame( 'https://client.example.com/wp-json/alynt-drime-backups-uploader/v2/action-intents', $captured['url'] );
		$this->assertSame( 'POST', $captured['args']['method'] );
		$this->assertSame( 'application/json', $captured['args']['headers']['Content-Type'] );
		$this->assertSame( 'ak_test', $captured['args']['headers']['X-Adbd-Action-Key-Id'] );
		$this->assertStringStartsWith( 'sig_', $captured['args']['headers']['X-Adbd-Action-Signature'] );
		$request_body = json_decode( $captured['args']['body'], true );
		$this->assertSame( $request_body['action_id'], $this->wpdb->inserted_data['public_id'] );
		$this->assertSame( 'scan_upload_now', $this->wpdb->inserted_data['action_type'] );
		$last_update = end( $this->wpdb->updates );
		$this->assertSame( 'accepted', $last_update['data']['state'] );
		$this->assertStringNotContainsString( 'private-key', wp_json_encode( $this->wpdb->inserted_data ) );
	}

}
