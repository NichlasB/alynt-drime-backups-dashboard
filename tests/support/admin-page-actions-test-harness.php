<?php
/**
 * Admin page action test harness.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/admin-page-actions-wordpress-shims.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-actions.php';
require_once __DIR__ . '/admin-page-actions-test-doubles.php';
require_once __DIR__ . '/admin-page-actions-handler-harness.php';
require_once __DIR__ . '/admin-page-actions-test-case-helpers.php';

/**
 * Shared admin action test setup.
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Action_Test_Case {
	use Alynt_Drime_Backups_Dashboard_Admin_Action_Test_Case_Helpers;

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

		$alynt_drime_backups_dashboard_test_nonce_action    = '';
		$alynt_drime_backups_dashboard_test_nonce_value     = '';
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

}
