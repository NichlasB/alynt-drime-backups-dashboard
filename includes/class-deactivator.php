<?php
/**
 * Deactivation tasks.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Deactivation handler.
 *
 * @since 0.1.0
 */
class Alynt_Drime_Backups_Dashboard_Deactivator {
	/**
	 * Runs on deactivation.
	 *
	 * @return void
	 */
	public static function deactivate() {
		wp_clear_scheduled_hook( 'alynt_drime_backups_dashboard_poll_sites' );
	}
}
