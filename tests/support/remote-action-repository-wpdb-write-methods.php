<?php
/**
 * Remote action repository wpdb write helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Provides insert/update capture shims for the remote-action repository wpdb double.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Remote_Action_WPDB_Write_Methods {
	/**
	 * Last insert table.
	 *
	 * @var string
	 */
	public $inserted_table = '';

	/**
	 * Last insert data.
	 *
	 * @var array<string,mixed>
	 */
	public $inserted_data = array();

	/**
	 * Last update data.
	 *
	 * @var array<string,mixed>
	 */
	public $updated_data = array();

	/**
	 * Last update where.
	 *
	 * @var array<string,mixed>
	 */
	public $updated_where = array();

	/**
	 * Insert shim.
	 *
	 * @param string              $table Table.
	 * @param array<string,mixed> $data Data.
	 * @param array<int,string>   $format Format.
	 * @return int|false
	 */
	public function insert( $table, $data, $format = array() ) {
		unset( $format );
		$this->inserted_table = $table;
		$this->inserted_data  = $data;

		return 1;
	}

	/**
	 * Update shim.
	 *
	 * @param string              $table Table.
	 * @param array<string,mixed> $data Data.
	 * @param array<string,mixed> $where Where.
	 * @return int
	 */
	public function update( $table, $data, $where ) {
		unset( $table );
		$this->updated_data  = $data;
		$this->updated_where = $where;

		return 1;
	}
}
