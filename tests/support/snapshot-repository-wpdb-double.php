<?php
/**
 * Snapshot repository wpdb test double.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Fake wpdb for snapshot repository tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Snapshot_WPDB {
	/**
	 * Table prefix.
	 *
	 * @var string
	 */
	public $prefix = 'wp_';

	/**
	 * Prepared arguments.
	 *
	 * @var array<int,mixed>
	 */
	public $prepared_args = array();

	/**
	 * Last query.
	 *
	 * @var string
	 */
	public $last_query = '';

	/**
	 * Rows returned by get_results().
	 *
	 * @var array<int,array<string,mixed>>
	 */
	public $result_rows = array();

	/**
	 * Prepares a query.
	 *
	 * @param string $query Query.
	 * @param mixed  ...$args Arguments.
	 * @return string
	 */
	public function prepare( $query, ...$args ) {
		$this->prepared_args = $args;

		return $query;
	}

	/**
	 * Runs a query.
	 *
	 * @param string $query Query.
	 * @return int
	 */
	public function query( $query ) {
		$this->last_query = $query;

		return 17;
	}

	/**
	 * Returns configured result rows.
	 *
	 * @param string $query Query.
	 * @param string $output Output format.
	 * @return array<int,array<string,mixed>>
	 */
	public function get_results( $query, $output ) {
		unset( $output );
		$this->last_query = $query;

		return $this->result_rows;
	}
}
