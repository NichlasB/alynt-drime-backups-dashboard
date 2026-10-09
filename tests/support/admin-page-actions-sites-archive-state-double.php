<?php
/**
 * Fake site repository archive-state methods for admin action tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Fake site repository archive-state behavior.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Sites_Archive_State {
	/**
	 * Archive calls.
	 *
	 * @var array<int,int>
	 */
	public $archive_calls = array();

	/**
	 * Unarchive calls.
	 *
	 * @var array<int,int>
	 */
	public $unarchive_calls = array();

	/**
	 * Archive result.
	 *
	 * @var bool
	 */
	public $archive_result = true;

	/**
	 * Unarchive result.
	 *
	 * @var bool
	 */
	public $unarchive_result = true;

	/**
	 * Records archive.
	 *
	 * @param int $site_id Site ID.
	 * @return bool
	 */
	public function archive_local( $site_id ) {
		$this->archive_calls[] = (int) $site_id;

		return $this->archive_result;
	}

	/**
	 * Records unarchive.
	 *
	 * @param int $site_id Site ID.
	 * @return bool
	 */
	public function unarchive_local( $site_id ) {
		$this->unarchive_calls[] = (int) $site_id;

		return $this->unarchive_result;
	}
}
