<?php
/**
 * Site repository fake wpdb write methods.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Fake wpdb write behavior for site repository tests.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Site_WPDB_Write_Methods {
	/**
	 * Result returned by update().
	 *
	 * @var int|false
	 */
	public $update_result = 1;

	/**
	 * Last updated table.
	 *
	 * @var string
	 */
	public $updated_table = '';

	/**
	 * Last updated data.
	 *
	 * @var array<string,mixed>
	 */
	public $updated_data = array();

	/**
	 * Last update where clause.
	 *
	 * @var array<string,mixed>
	 */
	public $updated_where = array();

	/**
	 * Updates a fake row.
	 *
	 * @param string              $table        Table.
	 * @param array<string,mixed> $data         Data.
	 * @param array<string,mixed> $where        Where clause.
	 * @param array<int,string>   $format       Data format.
	 * @param array<int,string>   $where_format Where format.
	 * @return int|false
	 */
	public function update( $table, $data, $where, $format = null, $where_format = null ) {
		unset( $format, $where_format );

		$this->updated_table = $table;
		$this->updated_data  = $data;
		$this->updated_where = $where;

		return $this->update_result;
	}
}
