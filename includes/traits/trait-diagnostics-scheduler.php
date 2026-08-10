<?php
/**
 * Diagnostics scheduler helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds scheduler diagnostics for the dashboard.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Scheduler {
	/**
	 * Builds scheduler diagnostics.
	 *
	 * @param int $now Current Unix timestamp.
	 * @return array<string,mixed>
	 */
	private function scheduler_diagnostics( $now ) {
		$poll_next    = $this->next_scheduled_at( Alynt_Drime_Backups_Dashboard_Poller::CRON_HOOK );
		$cleanup_next = $this->next_scheduled_at( Alynt_Drime_Backups_Dashboard_Poller::CLEANUP_HOOK );

		return array(
			'poll_hook'             => Alynt_Drime_Backups_Dashboard_Poller::CRON_HOOK,
			'cleanup_hook'          => Alynt_Drime_Backups_Dashboard_Poller::CLEANUP_HOOK,
			'recurrence'            => Alynt_Drime_Backups_Dashboard_Poller::CRON_RECURRENCE,
			'poll_interval_seconds' => Alynt_Drime_Backups_Dashboard_Poller::POLL_INTERVAL_SECONDS,
			'poll_batch_size'       => Alynt_Drime_Backups_Dashboard_Poller::DEFAULT_BATCH_SIZE,
			'stale_after_seconds'   => Alynt_Drime_Backups_Dashboard_Status_Classifier::DEFAULT_STALE_AFTER_SECONDS,
			'retention_days'        => Alynt_Drime_Backups_Dashboard_Poller::SNAPSHOT_RETENTION_DAYS,
			'cleanup_batch_size'    => Alynt_Drime_Backups_Dashboard_Poller::SNAPSHOT_CLEANUP_BATCH,
			'poll_next_at'          => $poll_next > 0 ? gmdate( 'Y-m-d H:i:s', $poll_next ) : '',
			'cleanup_next_at'       => $cleanup_next > 0 ? gmdate( 'Y-m-d H:i:s', $cleanup_next ) : '',
			'poll_schedule_state'   => $this->schedule_state( $poll_next, $now ),
			'cleanup_state'         => $this->schedule_state( $cleanup_next, $now ),
			'global_lock_active'    => $this->global_lock_active(),
			'current_utc'           => gmdate( 'Y-m-d H:i:s', $now ),
		);
	}

	/**
	 * Gets next scheduled timestamp for a hook.
	 *
	 * @param string $hook Cron hook.
	 * @return int
	 */
	private function next_scheduled_at( $hook ) {
		if ( ! function_exists( 'wp_next_scheduled' ) ) {
			return 0;
		}

		$next = wp_next_scheduled( $hook );

		return $next ? (int) $next : 0;
	}

	/**
	 * Builds a readable schedule state.
	 *
	 * @param int $next_scheduled Next scheduled timestamp.
	 * @param int $now Current Unix timestamp.
	 * @return string
	 */
	private function schedule_state( $next_scheduled, $now ) {
		if ( ! function_exists( 'wp_next_scheduled' ) ) {
			return 'unavailable';
		}

		if ( $next_scheduled <= 0 ) {
			return 'unscheduled';
		}

		if ( $next_scheduled < ( $now - Alynt_Drime_Backups_Dashboard_Poller::POLL_INTERVAL_SECONDS ) ) {
			return 'overdue';
		}

		return 'scheduled';
	}

	/**
	 * Determines whether the global scheduler lock is currently active.
	 *
	 * @return bool|null
	 */
	private function global_lock_active() {
		if ( ! function_exists( 'get_transient' ) ) {
			return null;
		}

		return false !== get_transient( Alynt_Drime_Backups_Dashboard_Poller::GLOBAL_LOCK_KEY );
	}
}
