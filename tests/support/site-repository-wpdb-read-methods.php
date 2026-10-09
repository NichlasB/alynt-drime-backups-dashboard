<?php
/**
 * Site repository fake wpdb read methods.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Fake wpdb read/query methods for site repository tests.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Site_WPDB_Read_Methods {
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
