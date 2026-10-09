<?php
/**
 * Poller site-repository due-for-poll helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Captures fake poller due-for-poll reads.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Poller_Site_Repository_Due_Methods {
	/**
	 * Due sites.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	public $due_sites = array();

	/**
	 * Last due-for-poll query.
	 *
	 * @var array<string,mixed>
	 */
	public $due_query = array();

	/**
	 * Gets due sites.
	 *
	 * @param int    $limit Limit.
	 * @param string $now Now.
	 * @return array<int,array<string,mixed>>
	 */
	public function due_for_poll( $limit = 5, $now = '' ) {
		$this->due_query = array(
			'limit' => $limit,
			'now'   => $now,
		);

		return array_slice( $this->due_sites, 0, (int) $limit );
	}
}
