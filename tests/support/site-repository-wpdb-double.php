<?php
/**
 * Site repository fake wpdb test double.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/site-repository-wpdb-write-methods.php';

/**
 * Fake wpdb for site repository tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Site_WPDB {
	use Alynt_Drime_Backups_Dashboard_Test_Site_WPDB_Write_Methods;

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
	 * Last output mode.
	 *
	 * @var string
	 */
	public $last_output = '';

	/**
	 * Row returned by get_row().
	 *
	 * @var array<string,mixed>|null
	 */
	public $row = null;

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
	 * Gets a fake row.
	 *
	 * @param string $query  Query.
	 * @param string $output Output mode.
	 * @return array<string,mixed>|null
	 */
	public function get_row( $query, $output = OBJECT ) {
		$this->last_query  = $query;
		$this->last_output = $output;

		return $this->row;
	}
}
