<?php
/**
 * Query-capture double for isolated history batching tests; never executes SQL.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/snapshot-repository-wpdb-double.php';

if ( ! defined( 'ARRAY_A' ) ) {
	define( 'ARRAY_A', 'ARRAY_A' );
}

/**
 * Captures prepared statements and returns explicitly supplied database responses.
 */
class Alynt_Drime_Backups_Dashboard_Test_History_Batch_WPDB extends Alynt_Drime_Backups_Dashboard_Test_Snapshot_WPDB {

	/** @var array<int,array{sql:string,args:array}> Prepared query captures. */
	public $prepared = array();

	/** @var array<int,string> Executed read captures. */
	public $queries = array();

	/** @var array<int,mixed> Supplied responses, including null/false failures. */
	public $responses = array();

	/**
	 * Captures both WordPress prepare argument forms without interpolating SQL.
	 *
	 * @param string $query Query template.
	 * @param mixed  ...$args Values.
	 * @return string
	 */
	public function prepare( $query, ...$args ) {
		$args = count( $args ) === 1 && is_array( $args[0] ) ? $args[0] : $args;
		$this->prepared[] = array( 'sql' => $query, 'args' => $args );
		return $query;
	}

	/**
	 * Returns the next supplied response. This is not a SQL interpreter.
	 *
	 * @param string $query Prepared query template.
	 * @param string $output Requested result format.
	 * @return mixed
	 */
	public function get_results( $query, $output ) {
		$this->queries[] = $query;
		return array_shift( $this->responses );
	}
}
