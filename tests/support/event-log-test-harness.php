<?php
/**
 * Event log test harness.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/event-log-option-shims.php';

if ( ! function_exists( 'get_current_user_id' ) ) {
	/**
	 * Test get_current_user_id shim.
	 *
	 * @return int
	 */
	function get_current_user_id() {
		global $alynt_drime_backups_dashboard_test_current_user_id;

		return (int) $alynt_drime_backups_dashboard_test_current_user_id;
	}
}

require_once dirname( __DIR__, 2 ) . '/includes/class-event-log-redactor.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-event-log-storage.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-event-log-settings.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-event-log-reporting.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-event-log.php';
