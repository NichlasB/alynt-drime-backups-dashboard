<?php
/**
 * Fake wpdb for remote action dispatcher tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Fake wpdb for dispatcher tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Dispatcher_WPDB {
	/**
	 * Prefix.
	 *
	 * @var string
	 */
	public $prefix = 'wp_';

	/**
	 * Insert ID.
	 *
	 * @var int
	 */
	public $insert_id = 44;

	/**
	 * Site row.
	 *
	 * @var array<string,mixed>
	 */
	public $site = array();

	/**
	 * Snapshot row.
	 *
	 * @var array<string,mixed>
	 */
	public $snapshot = array();

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
	 * Prepares SQL.
	 *
	 * @param string $query Query.
	 * @param mixed  ...$args Args.
	 * @return string
	 */
	public function prepare( $query, ...$args ) {
		unset( $args );
		return $query;
	}

	/**
	 * Gets a row.
	 *
	 * @param string $query Query.
	 * @param string $output Output.
	 * @return array<string,mixed>|null
	 */
	public function get_row( $query, $output = ARRAY_A ) {
		unset( $output );

		if ( false !== strpos( $query, 'alynt_drime_dashboard_snapshots' ) ) {
			return empty( $this->snapshot ) ? null : $this->snapshot;
		}

		return empty( $this->site ) ? null : $this->site;
	}

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
