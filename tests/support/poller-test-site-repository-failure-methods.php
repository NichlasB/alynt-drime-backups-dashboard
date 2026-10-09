<?php
/**
 * Poller site-repository failure write helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Captures fake poller repository failure write results.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Poller_Site_Repository_Failure_Methods {
	/**
	 * Failure data.
	 *
	 * @var array<string,mixed>
	 */
	public $failure = array();

	/**
	 * Failure rows.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	public $failures = array();

	/**
	 * Mark failure result.
	 *
	 * @var bool
	 */
	public $mark_failure_result = true;

	/**
	 * Marks failure.
	 *
	 * @param int    $site_id Site ID.
	 * @param string $error_code Error code.
	 * @param string $summary Summary.
	 * @param string $next_poll_at Next poll.
	 * @param int    $consecutive_failures Consecutive failures.
	 * @return bool
	 */
	public function mark_poll_failure( $site_id, $error_code, $summary = '', $next_poll_at = '', $consecutive_failures = 1 ) {
		$this->failure = array(
			'site_id'              => $site_id,
			'error_code'           => $error_code,
			'summary'              => $summary,
			'next_poll_at'         => $next_poll_at,
			'consecutive_failures' => $consecutive_failures,
		);
		$this->failures[] = $this->failure;

		return $this->mark_failure_result;
	}
}
