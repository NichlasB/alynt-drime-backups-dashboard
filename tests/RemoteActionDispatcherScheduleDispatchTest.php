<?php
/**
 * Remote action dispatcher schedule dispatch tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/remote-action-dispatcher-test-bootstrap.php';

/**
 * Tests signed remote schedule action dispatch.
 */
class RemoteActionDispatcherScheduleDispatchTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_Test_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_WPDB_Setup;

	public function test_schedule_preview_dispatch_posts_signed_preview_intent() {
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
						'summary'          => 'Schedule preview accepted safely.',
						'retry_after'      => 0,
					)
				),
			);
		};

		$result = $this->dispatcher( $http )->request_schedule_preview( 9, 'alynt_scan_upload', 'every_30_minutes', 7 );

		$this->assertIsArray( $result );
		$this->assertSame( 'schedule_preview', $result['action'] );
		$this->assertSame( 'accepted', $result['remote_state'] );
		$request_body = json_decode( $captured['args']['body'], true );
		$this->assertSame( 'schedule_preview', $request_body['action_type'] );
		$this->assertSame( 'alynt_scan_upload', $request_body['schedule_preview']['schedule_id'] );
		$this->assertSame( 'every_30_minutes', $request_body['schedule_preview']['proposed_cadence'] );
		$this->assertSame( 1, $request_body['schedule_preview']['capability_version'] );
		$this->assertSame( 'schedule_preview', $this->wpdb->inserted_data['action_type'] );
		$this->assertStringContainsString( 'every_30_minutes', $this->wpdb->inserted_data['redacted_context_json'] );
		$this->assertStringNotContainsString( 'private-key', wp_json_encode( $this->wpdb->inserted_data ) );
	}

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
		$http                 = function ( $url, $args ) use ( &$captured ) {
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
						'result_summary'   => 'Schedule apply accepted safely.',
						'retry_after'      => 0,
					)
				),
			);
		};

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

	public function test_schedule_rollback_preview_dispatch_posts_signed_non_mutating_intent_from_successful_apply() {
		$this->wpdb->snapshot = $this->snapshot_row(
			array(
				'allowed_actions'             => array( 'scan_upload_now', 'schedule_preview', 'schedule_apply', 'schedule_rollback_preview' ),
				'preview_only'                => false,
				'apply_supported'             => true,
				'rollback_preview_supported'  => true,
				'rollback_supported'          => false,
			)
		);
		$actions              = new Alynt_Drime_Backups_Dashboard_Test_Dispatcher_Actions();
		$captured             = array();
		$http                 = function ( $url, $args ) use ( &$captured ) {
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
						'result_summary'   => 'Schedule rollback preview accepted safely.',
						'retry_after'      => 0,
					)
				),
			);
		};

		$result = $this->dispatcher( $http, null, $actions )->request_schedule_rollback_preview( 9, '33333333-3333-4333-8333-333333333333', 7 );

		$this->assertIsArray( $result );
		$this->assertSame( 'schedule_rollback_preview', $result['action'] );
		$this->assertSame( 'accepted', $result['remote_state'] );
		$request_body = json_decode( $captured['args']['body'], true );
		$this->assertSame( 'schedule_rollback_preview', $request_body['action_type'] );
		$this->assertSame( 'alynt_scan_upload', $request_body['schedule_rollback_preview']['schedule_id'] );
		$this->assertSame( '33333333-3333-4333-8333-333333333333', $request_body['schedule_rollback_preview']['source_apply_action_id'] );
		$this->assertSame( str_repeat( 'b', 64 ), $request_body['schedule_rollback_preview']['rollback_metadata_fingerprint'] );
		$this->assertSame( 1, $request_body['schedule_rollback_preview']['capability_version'] );
		$this->assertArrayNotHasKey( 'schedule_rollback', $request_body );
		$this->assertArrayNotHasKey( 'proposed_cadence', $request_body['schedule_rollback_preview'] );
		$this->assertSame( 'schedule_rollback_preview', $actions->requests[0]['action_type'] );
		$this->assertTrue( $actions->requests[0]['context']['preview_only'] );
		$this->assertTrue( $actions->requests[0]['context']['non_mutating'] );
		$this->assertStringNotContainsString( 'private-key', wp_json_encode( $request_body ) );
	}
}
