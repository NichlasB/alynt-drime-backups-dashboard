<?php
/**
 * Remote action repository wpdb query helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/remote-action-repository-wpdb-read-methods.php';

/**
 * Provides query/read shims for the remote-action repository wpdb double.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Remote_Action_WPDB_Query_Methods {
	use Alynt_Drime_Backups_Dashboard_Test_Remote_Action_WPDB_Read_Methods;

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
