<?php
/**
 * Admin diagnostics overview scheduler-only fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared diagnostics overview scheduler-only payload fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Overview_Scheduler_Only_Fixtures {
	/**
	 * Returns an otherwise-empty diagnostics payload with scheduler data.
	 *
	 * @param array<string,mixed> $scheduler Scheduler diagnostics.
	 * @return array<string,mixed>
	 */
	private function scheduler_only_diagnostics( $scheduler ) {
		return array(
			'scheduler' => $scheduler,
			'counts'    => array(),
			'recent'    => array(),
			'logging'   => array(),
			'support'   => array(),
		);
	}
}
