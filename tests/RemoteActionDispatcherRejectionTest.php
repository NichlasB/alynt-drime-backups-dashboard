<?php
/**
 * Remote action dispatcher rejection tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/remote-action-dispatcher-test-bootstrap.php';

/**
 * Tests remote action dispatcher rejection paths before HTTP dispatch.
 */
class RemoteActionDispatcherRejectionTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_Test_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_WPDB_Setup;

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
}
