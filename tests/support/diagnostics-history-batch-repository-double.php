<?php
/**
 * Diagnostics integration collaborator that forbids single-site history reads.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Records batch requests while using the existing in-memory history fixtures.
 */
class Alynt_Drime_Backups_Dashboard_Test_Diagnostics_History_Batch_Repository extends Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Snapshot_Repository {

	/** @var array<int,array> Batch requests. */
	public $batch_requests = array();

	/**
	 * Gets fixture histories, never SQL.
	 *
	 * @param array<int> $site_ids Site IDs.
	 * @param int        $limit Per-site limit.
	 * @return array<int,array>
	 */
	public function recent_by_site_ids( array $site_ids, $limit = 10 ) {
		$this->batch_requests[] = array( $site_ids, $limit );
		return parent::recent_by_site_ids( $site_ids, $limit );
	}

	/**
	 * Rejects the old N+1 path so an empty-history result cannot pass silently.
	 *
	 * @param int $site_id Site ID.
	 * @param int $limit Per-site limit.
	 * @return array
	 */
	public function recent_for_site( $site_id, $limit = 10 ) {
		throw new LogicException( 'Diagnostics must not request single-site histories.' );
	}
}
