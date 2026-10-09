<?php
/**
 * Remote action repository wpdb read helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Provides read shims for the remote-action repository wpdb double.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Remote_Action_WPDB_Read_Methods {
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
}
