<?php
/**
 * Admin page polling action tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-actions-test-harness.php';

/**
 * Tests admin dashboard-local polling action behavior.
 */
class AdminPagePollingActionsTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Admin_Action_Test_Case;

	/**
	 * Valid pause posts update only dashboard-local polling state and audit the action.
	 *
	 * @return void
	 */
	public function test_valid_pause_polling_nonce_delegates_to_local_site_repository() {
		$this->set_valid_nonce( 'alynt_drime_backups_dashboard_pause_polling' );

		$harness = $this->admin_action_harness();

		$_POST = array(
			'alynt_drime_backups_dashboard_action' => 'pause_polling',
			'_wpnonce'                            => 'valid',
			'dashboard_site_id'                   => '42',
		);

		$result = $harness->handle_for_test();

		$this->assertIsArray( $result );
		$this->assertSame( 'pause_polling', $result['action'] );
		$this->assertTrue( $result['success'] );
		$this->assertSame( array( 42 ), $harness->sites->pause_calls );
		$this->assertSame( array(), $harness->sites->resume_calls );
		$this->assertSame( 'pause_polling', $harness->event_log->audit_calls[0]['action'] );
		$this->assertSame( 'succeeded', $harness->event_log->audit_calls[0]['outcome'] );
	}

	/**
	 * Valid resume posts update only dashboard-local polling state and audit the action.
	 *
	 * @return void
	 */
	public function test_valid_resume_polling_nonce_delegates_to_local_site_repository() {
		$this->set_valid_nonce( 'alynt_drime_backups_dashboard_resume_polling' );

		$harness = $this->admin_action_harness();

		$_POST = array(
			'alynt_drime_backups_dashboard_action' => 'resume_polling',
			'_wpnonce'                            => 'valid',
			'dashboard_site_id'                   => '42',
		);

		$result = $harness->handle_for_test();

		$this->assertIsArray( $result );
		$this->assertSame( 'resume_polling', $result['action'] );
		$this->assertTrue( $result['success'] );
		$this->assertSame( array(), $harness->sites->pause_calls );
		$this->assertSame( array( 42 ), $harness->sites->resume_calls );
		$this->assertSame( 'resume_polling', $harness->event_log->audit_calls[0]['action'] );
		$this->assertSame( 'succeeded', $harness->event_log->audit_calls[0]['outcome'] );
	}

	/**
	 * Revoked dashboard records cannot be paused.
	 *
	 * @return void
	 */
	public function test_pause_polling_rejects_revoked_site_records() {
		$this->set_valid_nonce( 'alynt_drime_backups_dashboard_pause_polling' );

		$harness = $this->admin_action_harness();
		$harness->sites->site['enrollment_status'] = 'revoked';

		$_POST = array(
			'alynt_drime_backups_dashboard_action' => 'pause_polling',
			'_wpnonce'                            => 'valid',
			'dashboard_site_id'                   => '42',
		);

		$result = $harness->handle_for_test();

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'site_revoked', $result->get_error_code() );
		$this->assertSame( array(), $harness->sites->pause_calls );
		$this->assertSame( 'failed', $harness->event_log->audit_calls[0]['outcome'] );
	}
}
