<?php
/**
 * Remote action dispatcher client response tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/remote-action-dispatcher-test-bootstrap.php';

/**
 * Tests remote action dispatcher client response handling.
 */
class RemoteActionDispatcherClientResponseTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_Test_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_WPDB_Setup;

	public function test_mismatched_client_action_response_is_recorded_as_dispatch_failure() {
		$http = function ( $url, $args ) {
			unset( $url, $args );

			return array(
				'response' => array(
					'code' => 202,
				),
				'body'     => wp_json_encode(
					array(
						'protocol_version' => 2,
						'action_id'        => 'different-action-id',
						'state'            => 'accepted',
						'code'             => 'action_accepted',
						'summary'          => 'Accepted safely.',
						'retry_after'      => 0,
					)
				),
			);
		};

		$result = $this->dispatcher( $http )->request_scan_upload_now( 9, 7 );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'remote_action_response_mismatch', $result->get_error_code() );
		$this->assertSame( 'scan_upload_now', $this->wpdb->inserted_data['action_type'] );
		$last_update = end( $this->wpdb->updates );
		$this->assertSame( 'dispatch_failed', $last_update['data']['state'] );
		$this->assertSame( 'remote_action_response_mismatch', $last_update['data']['result_code'] );
		$this->assertArrayHasKey( 'completed_at', $last_update['data'] );
	}

	public function test_client_rate_limit_response_is_recorded_as_rate_limited() {
		$http = function ( $url, $args ) {
			unset( $url );
			$body = json_decode( $args['body'], true );

			return array(
				'response' => array(
					'code' => 429,
				),
				'body'     => wp_json_encode(
					array(
						'protocol_version' => 2,
						'action_id'        => $body['action_id'],
						'state'            => 'rate_limited',
						'code'             => 'action_rate_limited',
						'summary'          => 'Rate limited safely.',
						'retry_after'      => 3600,
					)
				),
			);
		};

		$result = $this->dispatcher( $http )->request_scan_upload_now( 9, 7 );

		$this->assertIsArray( $result );
		$this->assertSame( 'rate_limited', $result['remote_state'] );
		$this->assertSame( 3600, $result['retry_after'] );
		$last_update = end( $this->wpdb->updates );
		$this->assertSame( 'rate_limited', $last_update['data']['state'] );
		$this->assertSame( 3600, $last_update['data']['retry_after_seconds'] );
	}
}
