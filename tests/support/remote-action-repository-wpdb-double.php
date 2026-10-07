<?php
/**
 * Remote action repository wpdb test double.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Fake wpdb for remote action repository tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Remote_Action_WPDB {
	use Alynt_Drime_Backups_Dashboard_Test_Remote_Action_WPDB_Query_Methods;

	/**
	 * Table prefix.
	 *
	 * @var string
	 */
	public $prefix = 'wp_';

	/**
	 * Insert ID.
	 *
	 * @var int
	 */
	public $insert_id = 321;

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
