<?php
/**
 * Remote action repository test harness.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ARRAY_A' ) ) {
	define( 'ARRAY_A', 'ARRAY_A' );
}

if ( ! function_exists( 'current_time' ) ) {
	/**
	 * Test current_time shim.
	 *
	 * @param string $type Type.
	 * @param bool   $gmt GMT.
	 * @return string
	 */
	function current_time( $type, $gmt = false ) {
		unset( $type, $gmt );
		return '2099-01-01 00:00:00';
	}
}

/**
 * Fake wpdb for remote action repository tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Remote_Action_WPDB {
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

/**
 * Shared remote action repository test fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Test_Fixtures {
	/**
	 * Creates a fresh repository under test.
	 *
	 * @return Alynt_Drime_Backups_Dashboard_Remote_Action_Repository
	 */
	private function remote_action_repository() {
		return new Alynt_Drime_Backups_Dashboard_Remote_Action_Repository();
	}

	/**
	 * Returns a stored action row with no existing redacted context.
	 *
	 * @return array<string,mixed>
	 */
	private function empty_remote_action_row() {
		return array(
			'id'                    => 321,
			'redacted_context_json' => wp_json_encode( array() ),
		);
	}
}
