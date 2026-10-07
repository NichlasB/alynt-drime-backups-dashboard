<?php
/**
 * Remote action repository wpdb query helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Provides query/read shims for the remote-action repository wpdb double.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Remote_Action_WPDB_Query_Methods {
	/**
	 * Prepared query.
	 *
	 * @var string
	 */
	public $last_query = '';

	/**
	 * Prepared args.
	 *
	 * @var array<int,mixed>
	 */
	public $prepared_args = array();

	/**
	 * Result row.
	 *
	 * @var array<string,mixed>|null
	 */
	public $row = null;

	/**
	 * Result rows.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	public $rows = array();

	/**
	 * Query preparation shim.
	 *
	 * @param string $query Query.
	 * @param mixed  ...$args Args.
	 * @return string
	 */
	public function prepare( $query, ...$args ) {
		$this->last_query    = $query;
		$this->prepared_args = $args;

		return $query;
	}

	/**
	 * Row retrieval shim.
	 *
	 * @param string $query Query.
	 * @param string $output Output type.
	 * @return array<string,mixed>|null
	 */
	public function get_row( $query, $output = ARRAY_A ) {
		unset( $output );
		$this->last_query = $query;

		return $this->row;
	}

	/**
	 * Row list retrieval shim.
	 *
	 * @param string $query Query.
	 * @param string $output Output type.
	 * @return array<int,array<string,mixed>>
	 */
	public function get_results( $query, $output = ARRAY_A ) {
		unset( $query, $output );

		return $this->rows;
	}

	/**
	 * Generic query shim.
	 *
	 * @param string $query Query.
	 * @return int
	 */
	public function query( $query ) {
		unset( $query );

		return 2;
	}
}
