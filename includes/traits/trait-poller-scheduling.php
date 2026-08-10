<?php
/**
 * Poller scheduling helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides cron registration and retry timing helpers for the poller.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Poller_Scheduling {
	/**
	 * Registers the custom polling recurrence.
	 *
	 * @param array<string,array<string,mixed>> $schedules Cron schedules.
	 * @return array<string,array<string,mixed>>
	 */
	public static function cron_schedules( $schedules ) {
		if ( ! isset( $schedules[ self::CRON_RECURRENCE ] ) ) {
			$schedules[ self::CRON_RECURRENCE ] = array(
				'interval' => self::POLL_INTERVAL_SECONDS,
				'display'  => __( 'Every 15 minutes', 'alynt-drime-backups-dashboard' ),
			);
		}

		return $schedules;
	}

	/**
	 * Schedules dashboard maintenance events.
	 *
	 * @return void
	 */
	public static function schedule_events() {
		if ( function_exists( 'add_filter' ) ) {
			// phpcs:ignore WordPress.WP.CronInterval.ChangeDetected -- The interval value is registered in cron_schedules().
			add_filter( 'cron_schedules', array( __CLASS__, 'cron_schedules' ) );
		}

		if ( function_exists( 'wp_next_scheduled' ) && function_exists( 'wp_schedule_event' ) && ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time() + 60, self::CRON_RECURRENCE, self::CRON_HOOK );
		}

		if ( function_exists( 'wp_next_scheduled' ) && function_exists( 'wp_schedule_event' ) && ! wp_next_scheduled( self::CLEANUP_HOOK ) ) {
			wp_schedule_event( time() + 300, 'daily', self::CLEANUP_HOOK );
		}
	}

	/**
	 * Unschedules dashboard maintenance events.
	 *
	 * @return void
	 */
	public static function unschedule_events() {
		if ( function_exists( 'wp_clear_scheduled_hook' ) ) {
			wp_clear_scheduled_hook( self::CRON_HOOK );
			wp_clear_scheduled_hook( self::CLEANUP_HOOK );
		}

		if ( function_exists( 'delete_transient' ) ) {
			delete_transient( self::GLOBAL_LOCK_KEY );
		}
	}

	/**
	 * Calculates the next success poll time.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return string
	 */
	private function next_poll_after_success( array $site ) {
		return gmdate( 'Y-m-d H:i:s', time() + self::POLL_INTERVAL_SECONDS + $this->poll_jitter( $site, self::POLL_JITTER_SECONDS ) );
	}

	/**
	 * Calculates the next retry time after a failure.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @param int                 $failures Consecutive failures.
	 * @return string
	 */
	private function next_poll_after_failure( array $site, $failures ) {
		$exponent = max( 0, min( 10, (int) $failures - 1 ) );
		$delay    = min( self::FAILURE_BACKOFF_MAX, self::POLL_INTERVAL_SECONDS * ( 2 ** $exponent ) );

		return gmdate( 'Y-m-d H:i:s', time() + $delay + $this->poll_jitter( $site, self::POLL_JITTER_SECONDS ) );
	}

	/**
	 * Returns deterministic per-site jitter.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @param int                 $spread Maximum spread in seconds.
	 * @return int
	 */
	private function poll_jitter( array $site, $spread ) {
		$key  = ! empty( $site['public_id'] ) ? (string) $site['public_id'] : (string) ( isset( $site['id'] ) ? $site['id'] : 'site' );
		$hash = (int) sprintf( '%u', crc32( $key ) );

		return $spread > 0 ? $hash % ( $spread + 1 ) : 0;
	}
}
