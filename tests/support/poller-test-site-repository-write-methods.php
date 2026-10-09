<?php
/**
 * Poller site-repository write-result helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/poller-test-site-repository-failure-methods.php';

/**
 * Captures fake poller repository success write results.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Poller_Site_Repository_Write_Methods {
	use Alynt_Drime_Backups_Dashboard_Test_Poller_Site_Repository_Failure_Methods;

	/**
	 * Success data.
	 *
	 * @var array<string,mixed>
	 */
	public $success = array();

	/**
	 * Success rows.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	public $successes = array();

	/**
	 * Mark success result.
	 *
	 * @var bool
	 */
	public $mark_success_result = true;

	/**
	 * Marks success.
	 *
	 * @param int    $site_id Site ID.
	 * @param string $status Status.
	 * @param string $plugin_version Plugin version.
	 * @param string $next_poll_at Next poll.
	 * @return bool
	 */
	public function mark_poll_success( $site_id, $status, $plugin_version = '', $next_poll_at = '' ) {
		$this->success = array(
			'site_id'        => $site_id,
			'status'         => $status,
			'plugin_version' => $plugin_version,
			'next_poll_at'   => $next_poll_at,
		);
		$this->successes[] = $this->success;

		return $this->mark_success_result;
	}
}
