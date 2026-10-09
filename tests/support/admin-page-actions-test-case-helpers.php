<?php
/**
 * Admin page action test-case helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared admin action request helpers.
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Action_Test_Case_Helpers {
	/**
	 * Sets the valid nonce action/value for the next request.
	 *
	 * @param string $action Nonce action.
	 * @return void
	 */
	private function set_valid_nonce( $action ) {
		global $alynt_drime_backups_dashboard_test_nonce_action;
		global $alynt_drime_backups_dashboard_test_nonce_value;

		$alynt_drime_backups_dashboard_test_nonce_action = $action;
		$alynt_drime_backups_dashboard_test_nonce_value  = 'valid';
	}

	/**
	 * Creates an admin action harness.
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Enrollment_Manager|null $manager Optional manager.
	 * @return Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Harness
	 */
	private function admin_action_harness( $manager = null ) {
		if ( null === $manager ) {
			$manager = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Enrollment_Manager();
		}

		return new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Harness( $manager );
	}
}
