<?php
/**
 * Diagnostics repository test doubles.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Fake site repository for diagnostics tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Site_Repository extends Alynt_Drime_Backups_Dashboard_Site_Repository {
	/**
	 * Sites.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	private $sites;

	/**
	 * Constructor.
	 *
	 * @param array<int,array<string,mixed>> $sites Sites.
	 */
	public function __construct( array $sites ) {
		$this->sites = $sites;
	}

	/**
	 * Lists sites.
	 *
	 * @param array $args Query args.
	 * @return array<int,array<string,mixed>>
	 */
	public function all( $args = array() ) {
		unset( $args );

		return $this->sites;
	}
}

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

/**
 * Fake remote action repository for diagnostics tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Remote_Action_Repository extends Alynt_Drime_Backups_Dashboard_Remote_Action_Repository {
	/**
	 * Action counts keyed by site ID.
	 *
	 * @var array<int,int>
	 */
	private $action_counts;

	/**
	 * Non-terminal action counts keyed by site ID.
	 *
	 * @var array<int,int>
	 */
	private $non_terminal_counts;

	/**
	 * Constructor.
	 *
	 * @param array<int,int> $action_counts Action counts.
	 * @param array<int,int> $non_terminal_counts Non-terminal action counts.
	 */
	public function __construct( array $action_counts = array(), array $non_terminal_counts = array() ) {
		$this->action_counts       = $action_counts;
		$this->non_terminal_counts = $non_terminal_counts;
	}

	/**
	 * Counts retained actions for one site.
	 *
	 * @param int $site_id Site ID.
	 * @return int
	 */
	public function count_for_site( $site_id ) {
		$site_id = (int) $site_id;

		return isset( $this->action_counts[ $site_id ] ) ? (int) $this->action_counts[ $site_id ] : 0;
	}

	/**
	 * Counts non-terminal actions for one site.
	 *
	 * @param int $site_id Site ID.
	 * @return int
	 */
	public function count_non_terminal_for_site( $site_id ) {
		$site_id = (int) $site_id;

		return isset( $this->non_terminal_counts[ $site_id ] ) ? (int) $this->non_terminal_counts[ $site_id ] : 0;
	}

	/**
	 * Builds an empty support summary.
	 *
	 * @return array<string,mixed>
	 */
	public function support_summary() {
		return array();
	}
}
