<?php
/**
 * In-memory count collaborators rejecting the former per-record query path.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/** Records snapshot count batches; fixture data is owned by the parent. */
class Alynt_Drime_Backups_Dashboard_Test_Archived_Snapshots extends Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Snapshot_Repository {
	/** @var array<int,array> Batch requests. */
	public $requests = array();
	/**
	 * Reads fixture counts in bulk.
	 *
	 * @param array<int> $ids Site IDs.
	 * @return array<int,int>
	 */
	public function counts_by_site_ids( array $ids ) {
		$this->requests[] = $ids;
		return parent::counts_by_site_ids( $ids );
	}
	/**
	 * Rejects unexpected legacy calls in the healthy bulk integration path.
	 *
	 * @param int $site_id Site ID.
	 * @return int
	 */
	public function count_for_site( $site_id ) {
		throw new LogicException( 'Healthy diagnostics must batch snapshot counts.' );
	}
}

/** Records independent action total/non-terminal batches. */
class Alynt_Drime_Backups_Dashboard_Test_Archived_Actions extends Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Remote_Action_Repository {
	/** @var array<int,array> Batch requests. */
	public $requests = array();
	/**
	 * Reads the independent fixture count map.
	 *
	 * @param array<int> $ids Site IDs.
	 * @param bool       $non_terminal Select only non-terminal actions.
	 * @return array<int,int>
	 */
	public function counts_by_site_ids( array $ids, $non_terminal = false ) {
		$this->requests[] = array( $ids, $non_terminal );
		return parent::counts_by_site_ids( $ids, $non_terminal );
	}
	/**
	 * Rejects per-site total reads.
	 *
	 * @param int $site_id Site ID.
	 * @return int
	 */
	public function count_for_site( $site_id ) {
		throw new LogicException( 'Healthy diagnostics must batch total action counts.' );
	}
	/**
	 * Rejects per-site non-terminal reads.
	 *
	 * @param int $site_id Site ID.
	 * @return int
	 */
	public function count_non_terminal_for_site( $site_id ) {
		throw new LogicException( 'Healthy diagnostics must batch non-terminal counts.' );
	}
}
