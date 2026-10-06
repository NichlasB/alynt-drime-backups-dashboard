<?php
/**
 * Admin page remote action tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-actions-test-harness.php';

/**
 * Tests admin remote action behavior.
 */
class AdminPageRemoteActionsTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Admin_Action_Test_Case;

	/**
	 * Request Backup Now delegates after nonce validation and performs a read-only follow-up poll.
	 *
	 * @return void
	 */
	public function test_valid_request_backup_now_nonce_delegates_and_polls_after_acceptance() {
		$this->set_valid_nonce( 'alynt_drime_backups_dashboard_request_backup_now' );

		$harness = $this->admin_action_harness();

		$_POST = array(
			'alynt_drime_backups_dashboard_action' => 'request_backup_now',
			'_wpnonce'                            => 'valid',
			'dashboard_site_id'                   => '42',
		);

		$result = $harness->handle_for_test();

		$this->assertIsArray( $result );
		$this->assertSame( 'request_backup_now', $result['action'] );
		$this->assertTrue( $result['poll_after_dispatch'] );
		$this->assertSame( array( array( 'site_id' => 42, 'requested_by' => 77 ) ), $harness->remote_action_dispatcher->calls );
		$this->assertSame( array( 42 ), $harness->poller->calls );
		$this->assertSame( 'request_backup_now', $harness->event_log->audit_calls[0]['action'] );
	}

	/**
	 * Cleanup Preview delegates after nonce validation and performs a read-only follow-up poll.
	 *
	 * @return void
	 */
	public function test_valid_cleanup_preview_nonce_delegates_and_polls_after_acceptance() {
		$this->set_valid_nonce( 'alynt_drime_backups_dashboard_cleanup_preview' );

		$harness = $this->admin_action_harness();

		$_POST = array(
			'alynt_drime_backups_dashboard_action' => 'cleanup_preview',
			'_wpnonce'                            => 'valid',
			'dashboard_site_id'                   => '42',
		);

		$result = $harness->handle_for_test();

		$this->assertIsArray( $result );
		$this->assertSame( 'cleanup_preview', $result['action'] );
		$this->assertTrue( $result['poll_after_dispatch'] );
		$this->assertSame( array( array( 'site_id' => 42, 'requested_by' => 77 ) ), $harness->remote_action_dispatcher->calls );
		$this->assertSame( array( 42 ), $harness->poller->calls );
		$this->assertSame( 'cleanup_preview', $harness->event_log->audit_calls[0]['action'] );
		$this->assertSame( 'cleanup_preview', $harness->event_log->audit_calls[0]['context']['action_type'] );
	}
}
