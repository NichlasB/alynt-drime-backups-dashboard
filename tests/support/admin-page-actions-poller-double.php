<?php
/**
 * Fake poller collaborator for admin action tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Fake poller.
 */
class Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Poller {
	/**
	 * Calls.
	 *
	 * @var array<int,int>
	 */
	public $calls = array();

	/**
	 * Checks status.
	 *
	 * @param int $site_id Site ID.
	 * @return array<string,mixed>
	 */
	public function check_status_now( $site_id ) {
		$this->calls[] = (int) $site_id;

		return array(
			'category' => 'working',
		);
	}
}
