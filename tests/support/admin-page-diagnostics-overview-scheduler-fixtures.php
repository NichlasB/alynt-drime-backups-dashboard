<?php
/**
 * Admin diagnostics overview scheduler fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared diagnostics overview scheduler fixture builders.
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Overview_Scheduler_Fixtures {
	/**
	 * Returns scheduler diagnostics for the overview fixture.
	 *
	 * @return array<string,mixed>
	 */
	private function overview_scheduler_diagnostics() {
		return array(
			'poll_hook'             => 'alynt_drime_backups_dashboard_poll_sites',
			'poll_schedule_state'   => 'scheduled',
			'poll_next_at'          => '2026-09-18 12:15:00',
			'poll_interval_seconds' => 900,
			'poll_batch_size'       => 5,
			'stale_after_seconds'   => 3600,
			'global_lock_active'    => false,
			'current_utc'           => '2026-09-18 12:00:00',
			'cleanup_hook'          => 'alynt_drime_backups_dashboard_cleanup_snapshots',
			'cleanup_state'         => 'scheduled',
			'cleanup_next_at'       => '2026-09-19 00:00:00',
			'retention_days'        => 30,
			'cleanup_batch_size'    => 100,
		);
	}
}
