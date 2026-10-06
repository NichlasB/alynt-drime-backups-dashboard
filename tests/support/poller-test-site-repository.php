<?php
/**
 * Poller site-repository test double.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Fake site repository for poller tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Poller_Site_Repository extends Alynt_Drime_Backups_Dashboard_Site_Repository {
	/**
	 * Site.
	 *
	 * @var array<string,mixed>|null
	 */
	public $site;

	/**
	 * Sites keyed by ID.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	public $sites = array();

	/**
	 * Due sites.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	public $due_sites = array();

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
	 * Mark success result.
	 *
	 * @var bool
	 */
	public $mark_success_result = true;

	/**
	 * Mark failure result.
	 *
	 * @var bool
	 */
	public $mark_failure_result = true;

	/**
	 * Last due-for-poll query.
	 *
	 * @var array<string,mixed>
	 */
	public $due_query = array();

	/**
	 * Constructor.
	 *
	 * @param array<string,mixed>|array<int,array<string,mixed>>|null $site Site.
	 */
	public function __construct( $site ) {
		if ( is_array( $site ) && isset( $site[0] ) && is_array( $site[0] ) ) {
			foreach ( $site as $row ) {
				$this->sites[ (int) $row['id'] ] = $row;
			}

			$this->due_sites = array_values( $this->sites );
			$this->site      = reset( $this->sites );
		} else {
			$this->site = $site;

			if ( is_array( $site ) && isset( $site['id'] ) ) {
				$this->sites[ (int) $site['id'] ] = $site;
			}
		}
	}

	/**
	 * Gets the fake site.
	 *
	 * @param int $site_id Site ID.
	 * @return array<string,mixed>|null
	 */
	public function get( $site_id ) {
		return isset( $this->sites[ (int) $site_id ] ) ? $this->sites[ (int) $site_id ] : null;
	}

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
