<?php
/**
 * Diagnostics snapshot repository history helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Fake retained snapshot history behavior for diagnostics tests.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Snapshot_History_Methods {
	/**
	 * Gets recent snapshots for one site.
	 *
	 * @param int $site_id Site ID.
	 * @param int $limit Maximum snapshots.
	 * @return array<int,array<string,mixed>>
	 */
	public function recent_for_site( $site_id, $limit = 10 ) {
		$site_id = (int) $site_id;

		if ( empty( $this->histories[ $site_id ] ) ) {
			return array();
		}

		return array_slice( $this->histories[ $site_id ], 0, max( 1, min( 50, (int) $limit ) ) );
	}

	/**
	 * Counts retained snapshots for one site.
	 *
	 * @param int $site_id Site ID.
	 * @return int
	 */
	public function count_for_site( $site_id ) {
		$site_id = (int) $site_id;

		if ( empty( $this->histories[ $site_id ] ) ) {
			return isset( $this->snapshots[ $site_id ] ) ? 1 : 0;
		}

		return count( $this->histories[ $site_id ] );
	}
}
