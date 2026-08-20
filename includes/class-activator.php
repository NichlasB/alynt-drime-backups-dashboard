<?php
/**
 * Activation tasks.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Activation handler.
 *
 * @since 0.1.0
 */
class Alynt_Drime_Backups_Dashboard_Activator {
	/**
	 * Runs on activation.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public static function activate() {
		Alynt_Drime_Backups_Dashboard_Storage::maybe_install();
		$scheduled = Alynt_Drime_Backups_Dashboard_Poller::schedule_events();

		if ( is_wp_error( $scheduled ) ) {
			wp_die( esc_html( $scheduled->get_error_message() ) );
		}
	}
}
