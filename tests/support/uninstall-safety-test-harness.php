<?php
/**
 * Uninstall safety test harness.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/uninstall-safety-wpdb-double.php';

if ( ! class_exists( 'Alynt_Drime_Backups_Dashboard_Uninstall_Safety_Recorder' ) ) {
	/**
	 * Records lifecycle operations for uninstall safety tests.
	 */
	class Alynt_Drime_Backups_Dashboard_Uninstall_Safety_Recorder {
		/**
		 * Recorded cleared hooks.
		 *
		 * @var string[]
		 */
		public static $cleared_hooks = array();

		/**
		 * Recorded deleted transients.
		 *
		 * @var string[]
		 */
		public static $deleted_transients = array();

		/**
		 * Recorded deleted options.
		 *
		 * @var string[]
		 */
		public static $deleted_options = array();

		/**
		 * Resets recorded lifecycle operations.
		 *
		 * @return void
		 */
		public static function reset() {
			self::$cleared_hooks      = array();
			self::$deleted_transients = array();
			self::$deleted_options    = array();
		}
	}
}

if ( ! function_exists( 'wp_clear_scheduled_hook' ) ) {
	/**
	 * Records an unscheduled hook for lifecycle tests.
	 *
	 * @param string $hook Hook name.
	 * @return void
	 */
	function wp_clear_scheduled_hook( $hook ) {
		Alynt_Drime_Backups_Dashboard_Uninstall_Safety_Recorder::$cleared_hooks[] = $hook;
	}
}

if ( ! function_exists( 'delete_transient' ) ) {
	/**
	 * Records a deleted transient for lifecycle tests.
	 *
	 * @param string $transient Transient name.
	 * @return bool
	 */
	function delete_transient( $transient ) {
		Alynt_Drime_Backups_Dashboard_Uninstall_Safety_Recorder::$deleted_transients[] = $transient;
		return true;
	}
}

if ( ! function_exists( 'delete_option' ) ) {
	/**
	 * Records a deleted option for lifecycle tests.
	 *
	 * @param string $option Option name.
	 * @return bool
	 */
	function delete_option( $option ) {
		Alynt_Drime_Backups_Dashboard_Uninstall_Safety_Recorder::$deleted_options[] = $option;
		return true;
	}
}
