<?php
/**
 * Query-capture collaborator for isolated grouped count/fallback tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/snapshot-history-batch-wpdb-double.php';

/** Captures legacy scalar fallback reads without executing SQL. */
class Alynt_Drime_Backups_Dashboard_Test_Count_Batch_WPDB extends Alynt_Drime_Backups_Dashboard_Test_History_Batch_WPDB {
	/** @var string Current isolated database error. */
	public $last_error = '';
	/** @var array<int,string> Error state paired with each grouped response. */
	public $response_errors = array();
	/** @var array<int,mixed> Supplied scalar responses. */
	public $fallback_values = array();
	/** @var array<int,string> Scalar query captures. */
	public $fallback_queries = array();

	/**
	 * Models ARRAY_A returning an empty array on error, unlike a null-only fake.
	 *
	 * @param string $query Prepared template.
	 * @param string $output Output mode.
	 * @return mixed
	 */
	public function get_results( $query, $output ) {
		$this->last_error = array_shift( $this->response_errors ) ?? '';
		return parent::get_results( $query, $output );
	}

	/**
	 * Returns an explicitly supplied scalar, not a SQL-interpreted count.
	 *
	 * @param string $query Prepared template.
	 * @return mixed
	 */
	public function get_var( $query ) {
		$this->last_error = '';
		$this->fallback_queries[] = $query;
		return array_shift( $this->fallback_values );
	}
}
