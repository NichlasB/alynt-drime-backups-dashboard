<?php
/**
 * Remote-action counting repository double for admin polling-state rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Counts remote-action rows for local record rendering tests.
 */
class Alynt_Drime_Backups_Dashboard_Counting_Remote_Action_Repository_Test_Double {
	/**
	 * Action row count.
	 *
	 * @var int
	 */
	private $count;

	/**
	 * Non-terminal action row count.
	 *
	 * @var int
	 */
	private $non_terminal_count;

	/**
	 * Constructor.
	 *
	 * @param int $count Action row count.
	 * @param int $non_terminal_count Non-terminal action row count.
	 */
	public function __construct( $count, $non_terminal_count ) {
		$this->count              = max( 0, (int) $count );
		$this->non_terminal_count = max( 0, (int) $non_terminal_count );
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

	/**
	 * Counts non-terminal rows for one site.
	 *
	 * @param int $site_id Site ID.
	 * @return int
	 */
	public function count_non_terminal_for_site( $site_id ) {
		unset( $site_id );

		return $this->non_terminal_count;
	}
}
