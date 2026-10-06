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
	use Alynt_Drime_Backups_Dashboard_Admin_Action_Test_Case;

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
		$this->set_valid_nonce( 'alynt_drime_backups_dashboard_create_pending_site' );

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
