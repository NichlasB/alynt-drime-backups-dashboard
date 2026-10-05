<?php
/**
 * Remote action dispatcher tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

if ( ! defined( 'ARRAY_A' ) ) {
	define( 'ARRAY_A', 'ARRAY_A' );
}

if ( ! function_exists( 'current_time' ) ) {
	/**
	 * Test current_time shim.
	 *
	 * @param string $type Type.
	 * @param bool   $gmt GMT.
	 * @return string
	 */
	function current_time( $type, $gmt = false ) {
		unset( $type, $gmt );
		return '2099-01-01 00:00:00';
	}
}

if ( ! function_exists( 'home_url' ) ) {
	/**
	 * Test home_url shim.
	 *
	 * @param string      $path Path.
	 * @param string|null $scheme Scheme.
	 * @return string
	 */
	function home_url( $path = '', $scheme = null ) {
		unset( $scheme );

		return 'https://control.sitesmanage.com' . $path;
	}
}
require_once __DIR__ . '/support/remote-action-dispatcher-test-harness.php';

/**
 * Tests signed remote action dispatch.
 */
class RemoteActionDispatcherTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_Test_Fixtures;

	/**
	 * Previous wpdb.
	 *
	 * @var mixed
	 */
	private $previous_wpdb;

	/**
	 * Fake wpdb.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Test_Dispatcher_WPDB
	 */
	private $wpdb;

	protected function setUp(): void {
		parent::setUp();

		global $wpdb;

		$this->previous_wpdb = $wpdb;
		$this->wpdb          = new Alynt_Drime_Backups_Dashboard_Test_Dispatcher_WPDB();
		$this->wpdb->site    = $this->site_row();
		$this->wpdb->snapshot = $this->snapshot_row();
		$wpdb                = $this->wpdb;
	}

	protected function tearDown(): void {
		global $wpdb;

		$wpdb = $this->previous_wpdb;

		parent::tearDown();
	}

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
				'allowed_actions'     => array( 'scan_upload_now', 'schedule_preview', 'schedule_apply' ),
				'preview_only'        => false,
				'apply_supported'     => true,
				'rollback_supported'  => false,
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

	public function test_schedule_preview_rejects_unsupported_cadence_without_http_dispatch() {
		$called = false;
		$http   = function () use ( &$called ) {
			$called = true;
			return array();
		};

		$result = $this->dispatcher( $http )->request_schedule_preview( 9, 'alynt_scan_upload', 'raw_cron', 7 );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'schedule_management_unavailable', $result->get_error_code() );
		$this->assertFalse( $called );
		$this->assertSame( array(), $this->wpdb->inserted_data );
	}

	public function test_schedule_rollback_preview_rejects_missing_capability_without_http_dispatch() {
		$this->wpdb->snapshot = $this->snapshot_row(
			array(
				'allowed_actions'             => array( 'scan_upload_now', 'schedule_preview', 'schedule_apply' ),
				'preview_only'                => false,
				'apply_supported'             => true,
				'rollback_preview_supported'  => false,
				'rollback_supported'          => false,
			)
		);
		$actions              = new Alynt_Drime_Backups_Dashboard_Test_Dispatcher_Actions();
		$called               = false;
		$http                 = function () use ( &$called ) {
			$called = true;
			return array();
		};

		$result = $this->dispatcher( $http, null, $actions )->request_schedule_rollback_preview( 9, '33333333-3333-4333-8333-333333333333', 7 );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'schedule_rollback_preview_unavailable', $result->get_error_code() );
		$this->assertFalse( $called );
		$this->assertSame( array(), $this->wpdb->inserted_data );
		$this->assertSame( array(), $actions->requests );
	}

	public function test_missing_capability_fails_without_http_dispatch() {
		$this->wpdb->snapshot = array(
			'payload_json' => wp_json_encode(
				array(
					'remote_actions' => array(
						'protocol_version' => 2,
						'enabled'          => false,
					),
				)
			),
		);
		$called = false;
		$http   = function () use ( &$called ) {
			$called = true;
			return array();
		};

		$result = $this->dispatcher( $http )->request_scan_upload_now( 9, 7 );

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'remote_action_capability_missing', $result->get_error_code() );
		$this->assertFalse( $called );
		$this->assertSame( array(), $this->wpdb->inserted_data );
	}

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
