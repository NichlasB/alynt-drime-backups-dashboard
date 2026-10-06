<?php
/**
 * Uninstall safety test harness.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! function_exists( 'wp_clear_scheduled_hook' ) ) {
	/**
	 * Records an unscheduled hook for lifecycle tests.
	 *
	 * @param string $hook Hook name.
	 * @return void
	 */
	function wp_clear_scheduled_hook( $hook ) {
		Alynt_Drime_Backups_Dashboard_Uninstall_Safety_Test::$cleared_hooks[] = $hook;
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
		Alynt_Drime_Backups_Dashboard_Uninstall_Safety_Test::$deleted_transients[] = $transient;
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
		Alynt_Drime_Backups_Dashboard_Uninstall_Safety_Test::$deleted_options[] = $option;
		return true;
	}
}

/**
 * Minimal database double for uninstall regression coverage.
 */
class Alynt_Drime_Backups_Dashboard_Uninstall_Safety_Wpdb {
	/**
	 * WordPress table prefix.
	 *
	 * @var string
	 */
	public $prefix = 'wp_';

	/**
	 * WordPress options table name.
	 *
	 * @var string
	 */
	public $options = 'wp_options';

	/**
	 * Captured database queries.
	 *
	 * @var string[]
	 */
	public $queries = array();

	/**
	 * Returns a LIKE-safe value for test purposes.
	 *
	 * @param string $value Value.
	 * @return string
	 */
	public function esc_like( $value ) {
		return $value;
	}

	/**
	 * Returns the query because test values do not affect query classification.
	 *
	 * @param string $query Query.
	 * @param mixed  ...$values Prepared values.
	 * @return string
	 */
	public function prepare( $query, ...$values ) {
		unset( $values );
		return $query;
	}

	/**
	 * Captures a database query.
	 *
	 * @param string $query Query.
	 * @return int
	 */
	public function query( $query ) {
		$this->queries[] = $query;
		return 1;
	}
}
