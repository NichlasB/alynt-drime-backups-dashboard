<?php
/**
 * Admin page action tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-actions-test-harness.php';

/**
 * Tests admin action behavior.
 */
class AdminPageActionsTest extends TestCase {
	/**
	 * Original POST data.
	 *
	 * @var array<string,mixed>
	 */
	private $previous_post = array();

	/**
	 * Resets globals.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		global $alynt_drime_backups_dashboard_test_nonce_action;
		global $alynt_drime_backups_dashboard_test_nonce_value;
		global $alynt_drime_backups_dashboard_test_current_user_id;

		$this->previous_post = $_POST;
		$_POST              = array();

		$alynt_drime_backups_dashboard_test_nonce_action = '';
		$alynt_drime_backups_dashboard_test_nonce_value  = '';
		$alynt_drime_backups_dashboard_test_current_user_id = 77;
	}

	/**
	 * Restores globals.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		$_POST = $this->previous_post;

		parent::tearDown();
	}

	/**
	 * Invalid form nonces return a recoverable error and do not process payloads.
	 *
	 * @return void
	 */
	public function test_expired_create_pending_nonce_returns_recovery_error_without_delegating() {
		$manager = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Enrollment_Manager();
		$harness = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Harness( $manager );

		$_POST = array(
			'alynt_drime_backups_dashboard_action'       => 'create_pending_site',
			'_wpnonce'                                  => 'expired',
			'alynt_drime_backups_dashboard_pending_site' => array(
				'expected_origin' => 'https://client.example.com',
			),
		);

		$result = $harness->handle_for_test();

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'dashboard_session_expired', $result->get_error_code() );
		$this->assertSame( array(), $manager->calls );
	}

	/**
	 * Valid create-site posts delegate only after the action-specific nonce passes.
	 *
	 * @return void
	 */
	public function test_valid_create_pending_nonce_delegates_payload_and_dashboard_origin() {
		global $alynt_drime_backups_dashboard_test_nonce_action;
		global $alynt_drime_backups_dashboard_test_nonce_value;

		$alynt_drime_backups_dashboard_test_nonce_action = 'alynt_drime_backups_dashboard_create_pending_site';
		$alynt_drime_backups_dashboard_test_nonce_value  = 'valid';

		$manager = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Enrollment_Manager();
		$harness = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Harness( $manager );

		$_POST = array(
			'alynt_drime_backups_dashboard_action'       => 'create_pending_site',
			'_wpnonce'                                  => 'valid',
			'alynt_drime_backups_dashboard_pending_site' => array(
				'site_label'      => 'Client Site',
				'expected_origin' => 'https://client.example.com',
				'environment'     => 'staging',
			),
		);

		$result = $harness->handle_for_test();

		$this->assertIsArray( $result );
		$this->assertSame( 'adb1.test', $result['pairing_token'] );
		$this->assertCount( 1, $manager->calls );
		$this->assertSame( $_POST['alynt_drime_backups_dashboard_pending_site'], $manager->calls[0]['raw'] );
		$this->assertSame( 'https://control.sitesmanage.com/', $manager->calls[0]['dashboard_origin'] );
	}

	/**
	 * Request Backup Now delegates after nonce validation and performs a read-only follow-up poll.
	 *
	 * @return void
	 */
	public function test_valid_request_backup_now_nonce_delegates_and_polls_after_acceptance() {
		global $alynt_drime_backups_dashboard_test_nonce_action;
		global $alynt_drime_backups_dashboard_test_nonce_value;

		$alynt_drime_backups_dashboard_test_nonce_action = 'alynt_drime_backups_dashboard_request_backup_now';
		$alynt_drime_backups_dashboard_test_nonce_value  = 'valid';

		$manager = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Enrollment_Manager();
		$harness = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Harness( $manager );

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
		global $alynt_drime_backups_dashboard_test_nonce_action;
		global $alynt_drime_backups_dashboard_test_nonce_value;

		$alynt_drime_backups_dashboard_test_nonce_action = 'alynt_drime_backups_dashboard_cleanup_preview';
		$alynt_drime_backups_dashboard_test_nonce_value  = 'valid';

		$manager = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Enrollment_Manager();
		$harness = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Harness( $manager );

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

	/**
	 * Valid pause posts update only dashboard-local polling state and audit the action.
	 *
	 * @return void
	 */
	public function test_valid_pause_polling_nonce_delegates_to_local_site_repository() {
		global $alynt_drime_backups_dashboard_test_nonce_action;
		global $alynt_drime_backups_dashboard_test_nonce_value;

		$alynt_drime_backups_dashboard_test_nonce_action = 'alynt_drime_backups_dashboard_pause_polling';
		$alynt_drime_backups_dashboard_test_nonce_value  = 'valid';

		$manager = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Enrollment_Manager();
		$harness = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Harness( $manager );

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
		global $alynt_drime_backups_dashboard_test_nonce_action;
		global $alynt_drime_backups_dashboard_test_nonce_value;

		$alynt_drime_backups_dashboard_test_nonce_action = 'alynt_drime_backups_dashboard_resume_polling';
		$alynt_drime_backups_dashboard_test_nonce_value  = 'valid';

		$manager = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Enrollment_Manager();
		$harness = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Harness( $manager );

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
		global $alynt_drime_backups_dashboard_test_nonce_action;
		global $alynt_drime_backups_dashboard_test_nonce_value;

		$alynt_drime_backups_dashboard_test_nonce_action = 'alynt_drime_backups_dashboard_pause_polling';
		$alynt_drime_backups_dashboard_test_nonce_value  = 'valid';

		$manager = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Enrollment_Manager();
		$harness = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Harness( $manager );
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

	/**
	 * Revoked records can be archived locally without contacting client sites.
	 *
	 * @return void
	 */
	public function test_archive_local_allows_revoked_records() {
		global $alynt_drime_backups_dashboard_test_nonce_action;
		global $alynt_drime_backups_dashboard_test_nonce_value;

		$alynt_drime_backups_dashboard_test_nonce_action = 'alynt_drime_backups_dashboard_archive_local';
		$alynt_drime_backups_dashboard_test_nonce_value  = 'valid';

		$manager = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Enrollment_Manager();
		$harness = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Harness( $manager );
		$harness->sites->site['enrollment_status'] = 'revoked';

		$_POST = array(
			'alynt_drime_backups_dashboard_action' => 'archive_local',
			'_wpnonce'                            => 'valid',
			'dashboard_site_id'                   => '42',
		);

		$result = $harness->handle_for_test();

		$this->assertIsArray( $result );
		$this->assertSame( 'archive_local', $result['action'] );
		$this->assertTrue( $result['success'] );
		$this->assertSame( array( 42 ), $harness->sites->archive_calls );
		$this->assertSame( 'archive_local', $harness->event_log->audit_calls[0]['action'] );
		$this->assertSame( 'succeeded', $harness->event_log->audit_calls[0]['outcome'] );
	}

	/**
	 * Active enrolled records cannot be hidden with archive controls.
	 *
	 * @return void
	 */
	public function test_archive_local_rejects_active_records() {
		global $alynt_drime_backups_dashboard_test_nonce_action;
		global $alynt_drime_backups_dashboard_test_nonce_value;

		$alynt_drime_backups_dashboard_test_nonce_action = 'alynt_drime_backups_dashboard_archive_local';
		$alynt_drime_backups_dashboard_test_nonce_value  = 'valid';

		$manager = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Enrollment_Manager();
		$harness = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Harness( $manager );

		$_POST = array(
			'alynt_drime_backups_dashboard_action' => 'archive_local',
			'_wpnonce'                            => 'valid',
			'dashboard_site_id'                   => '42',
		);

		$result = $harness->handle_for_test();

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'site_archive_not_allowed', $result->get_error_code() );
		$this->assertSame( array(), $harness->sites->archive_calls );
		$this->assertSame( 'failed', $harness->event_log->audit_calls[0]['outcome'] );
	}

	/**
	 * Archived records can be unarchived locally without restoring credentials.
	 *
	 * @return void
	 */
	public function test_unarchive_local_delegates_to_repository() {
		global $alynt_drime_backups_dashboard_test_nonce_action;
		global $alynt_drime_backups_dashboard_test_nonce_value;

		$alynt_drime_backups_dashboard_test_nonce_action = 'alynt_drime_backups_dashboard_unarchive_local';
		$alynt_drime_backups_dashboard_test_nonce_value  = 'valid';

		$manager = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Enrollment_Manager();
		$harness = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Harness( $manager );
		$harness->sites->site['enrollment_status'] = 'revoked';
		$harness->sites->site['archived_at']        = '2026-09-19 18:30:00';

		$_POST = array(
			'alynt_drime_backups_dashboard_action' => 'unarchive_local',
			'_wpnonce'                            => 'valid',
			'dashboard_site_id'                   => '42',
		);

		$result = $harness->handle_for_test();

		$this->assertIsArray( $result );
		$this->assertSame( 'unarchive_local', $result['action'] );
		$this->assertTrue( $result['success'] );
		$this->assertSame( array( 42 ), $harness->sites->unarchive_calls );
		$this->assertSame( 'unarchive_local', $harness->event_log->audit_calls[0]['action'] );
	}

	/**
	 * Unsupported actions remain local errors.
	 *
	 * @return void
	 */
	public function test_unknown_action_returns_error_without_nonce_check() {
		$manager = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Enrollment_Manager();
		$harness = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Harness( $manager );

		$_POST = array(
			'alynt_drime_backups_dashboard_action' => 'remote_restore',
		);

		$result = $harness->handle_for_test();

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'dashboard_action_unknown', $result->get_error_code() );
		$this->assertSame( array(), $manager->calls );
	}
}
