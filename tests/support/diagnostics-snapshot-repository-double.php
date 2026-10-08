<?php
/**
 * Diagnostics snapshot repository test double.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Fake snapshot repository for diagnostics tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Snapshot_Repository extends Alynt_Drime_Backups_Dashboard_Snapshot_Repository {
	/**
	 * Snapshots keyed by site ID.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	private $snapshots;

	/**
	 * Recent snapshot histories keyed by site ID.
	 *
	 * @var array<int,array<int,array<string,mixed>>>
	 */
	private $histories;

	/**
	 * Constructor.
	 *
	 * @param array<int,array<string,mixed>>            $snapshots Snapshots.
	 * @param array<int,array<int,array<string,mixed>>> $histories Recent snapshot histories.
	 */
	public function __construct( array $snapshots, array $histories = array() ) {
		$this->snapshots = $snapshots;
		$this->histories = $histories;
	}

	/**
	 * Gets latest snapshots keyed by site ID.
	 *
	 * @param array<int> $site_ids Site IDs.
	 * @return array<int,array<string,mixed>>
	 */
	public function latest_by_site_ids( array $site_ids ) {
		$matched = array();

		foreach ( $site_ids as $site_id ) {
			if ( isset( $this->snapshots[ (int) $site_id ] ) ) {
				$matched[ (int) $site_id ] = $this->snapshots[ (int) $site_id ];
			}
		}

		return $matched;
	}

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
