<?php
/**
 * Remote action dispatcher schedule-apply dispatch tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/remote-action-dispatcher-test-bootstrap.php';

/**
 * Tests signed remote schedule-apply dispatch.
 */
class RemoteActionDispatcherScheduleApplyDispatchTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_Test_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_WPDB_Setup;

	public function test_schedule_apply_dispatch_posts_signed_apply_intent_from_fresh_preview() {
		$this->wpdb->snapshot = $this->snapshot_row(
			array(
				'allowed_actions'    => array( 'scan_upload_now', 'schedule_preview', 'schedule_apply' ),
				'preview_only'       => false,
				'apply_supported'    => true,
				'rollback_supported' => false,
			)
		);
		$actions              = new Alynt_Drime_Backups_Dashboard_Test_Dispatcher_Actions();
		$captured             = array();
		$http                 = $this->accepted_http_response( $captured, 'Schedule apply accepted safely.' );

		$result = $this->dispatcher( $http, null, $actions )->request_schedule_apply( 9, '22222222-2222-4222-8222-222222222222', 7 );

		$this->assertIsArray( $result );
		$this->assertSame( 'schedule_apply', $result['action'] );
		$this->assertSame( 'accepted', $result['remote_state'] );
		$request_body = json_decode( $captured['args']['body'], true );
		$this->assertSame( 'schedule_apply', $request_body['action_type'] );
		$this->assertSame( 'alynt_scan_upload', $request_body['schedule_apply']['schedule_id'] );
		$this->assertSame( 'every_30_minutes', $request_body['schedule_apply']['proposed_cadence'] );
		$this->assertSame( '22222222-2222-4222-8222-222222222222', $request_body['schedule_apply']['preview_action_id'] );
		$this->assertSame( str_repeat( 'a', 64 ), $request_body['schedule_apply']['preview_fingerprint'] );
		$this->assertSame( 'schedule_apply', $actions->requests[0]['action_type'] );
		$this->assertStringNotContainsString( 'private-key', wp_json_encode( $request_body ) );
	}

	/**
	 * Builds an accepted remote-action HTTP response callback.
	 *
	 * @param array<string,mixed> $captured Captured request details.
	 * @param string              $summary Response summary.
	 * @return callable
	 */
	private function accepted_http_response( array &$captured, $summary ) {
		return function ( $url, $args ) use ( &$captured, $summary ) {
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
						'result_code'      => 'action_accepted',
						'result_summary'   => $summary,
						'retry_after'      => 0,
					)
				),
			);
		};
	}
}
