<?php
/**
 * Diagnostics remote-action repository test double.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

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
