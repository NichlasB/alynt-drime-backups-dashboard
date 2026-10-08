<?php
/**
 * Uninstall safety database test double.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

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
