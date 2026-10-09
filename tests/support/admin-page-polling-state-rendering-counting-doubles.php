<?php
/**
 * Counting repository doubles for admin polling-state rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/admin-page-polling-state-rendering-remote-action-counting-double.php';

/**
 * Counts snapshot rows for local record rendering tests.
 */
class Alynt_Drime_Backups_Dashboard_Counting_Snapshot_Repository_Test_Double {
	/**
	 * Snapshot count.
	 *
	 * @var int
	 */
	private $count;

	/**
	 * Constructor.
	 *
	 * @param int $count Snapshot count.
	 */
	public function __construct( $count ) {
		$this->count = max( 0, (int) $count );
	}

	/**
	 * Counts rows for one site.
	 *
	 * @param int $site_id Site ID.
	 * @return int
	 */
	public function count_for_site( $site_id ) {
		unset( $site_id );

		return $this->count;
	}
}
