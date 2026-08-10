<?php
/**
 * Poller lock helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides transient-backed lock helpers for the poller.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Poller_Locks {
	/**
	 * Acquires the global scheduler lock.
	 *
	 * @return bool
	 */
	private function acquire_global_lock() {
		if ( ! function_exists( 'get_transient' ) || ! function_exists( 'set_transient' ) ) {
			return true;
		}

		if ( false !== get_transient( self::GLOBAL_LOCK_KEY ) ) {
			return false;
		}

		return (bool) set_transient( self::GLOBAL_LOCK_KEY, time(), self::LOCK_TTL_SECONDS );
	}

	/**
	 * Releases the global scheduler lock.
	 *
	 * @return void
	 */
	private function release_global_lock() {
		if ( function_exists( 'delete_transient' ) ) {
			delete_transient( self::GLOBAL_LOCK_KEY );
		}
	}

	/**
	 * Acquires a per-site poll lock.
	 *
	 * @param int $site_id Site ID.
	 * @return bool
	 */
	private function acquire_site_lock( $site_id ) {
		if ( ! function_exists( 'get_transient' ) || ! function_exists( 'set_transient' ) ) {
			return true;
		}

		$key = self::SITE_LOCK_KEY_PREFIX . absint( $site_id );

		if ( false !== get_transient( $key ) ) {
			return false;
		}

		return (bool) set_transient( $key, time(), self::LOCK_TTL_SECONDS );
	}

	/**
	 * Releases a per-site poll lock.
	 *
	 * @param int $site_id Site ID.
	 * @return void
	 */
	private function release_site_lock( $site_id ) {
		if ( function_exists( 'delete_transient' ) ) {
			delete_transient( self::SITE_LOCK_KEY_PREFIX . absint( $site_id ) );
		}
	}
}
