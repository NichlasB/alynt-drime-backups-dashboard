<?php
/**
 * Dashboard polling coordinator.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Coordinates read-only client status polling.
 *
 * @since 0.1.0
 */
class Alynt_Drime_Backups_Dashboard_Poller {
	/**
	 * Runs the scheduled poll.
	 *
	 * This is intentionally a no-op in the first scaffold. The implementation
	 * plan requires explicit protocol freeze and client opt-in work before any
	 * outbound requests are enabled.
	 *
	 * @return void
	 */
	public function poll_sites() {
		do_action( 'alynt_drime_backups_dashboard_poll_sites_noop' );
	}
}
