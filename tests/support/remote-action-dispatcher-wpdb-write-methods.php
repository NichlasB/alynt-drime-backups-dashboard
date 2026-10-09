<?php
/**
 * Fake wpdb write methods for remote action dispatcher tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Fake wpdb write behavior for dispatcher tests.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Dispatcher_WPDB_Write_Methods {
	/**
	 * Inserted action.
	 *
	 * @var array<string,mixed>
	 */
	public $inserted_data = array();

	/**
	 * Update calls.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	public $updates = array();

	/**
	 * Inserts a row.
	 *
	 * @param string              $table Table.
	 * @param array<string,mixed> $data Data.
	 * @return int
	 */
	public function insert( $table, $data ) {
		unset( $table );
		$this->inserted_data = $data;

		return 1;
	}

	/**
	 * Updates a row.
	 *
	 * @param string              $table Table.
	 * @param array<string,mixed> $data Data.
	 * @param array<string,mixed> $where Where.
	 * @return int
	 */
	public function update( $table, $data, $where ) {
		unset( $table );
		$this->updates[] = array(
			'data'  => $data,
			'where' => $where,
		);

		return 1;
	}
}
