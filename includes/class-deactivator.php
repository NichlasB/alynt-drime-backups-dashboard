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
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public static function deactivate() {
		Alynt_Drime_Backups_Dashboard_Poller::unschedule_events();
	}
}
