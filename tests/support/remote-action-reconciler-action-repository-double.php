<?php
/**
 * Fake action repository for remote action reconciler tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/remote-action-reconciler-stale-methods.php';

/**
 * Fake action repository for reconciliation tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Reconciler_Action_Repository extends Alynt_Drime_Backups_Dashboard_Remote_Action_Repository {
	use Alynt_Drime_Backups_Dashboard_Test_Reconciler_Stale_Methods;

	/**
	 * Stored row.
	 *
	 * @var array<string,mixed>|null
	 */
	public $stored = null;

	/**
	 * Last lookup.
	 *
	 * @var array<string,mixed>
	 */
	public $lookup = array();

	/**
	 * Last client report.
	 *
	 * @var array<string,mixed>
	 */
	public $client_report = array();

	/**
	 * Constructor.
	 */
	public function __construct() {}

	/**
	 * Finds by public ID and site.
	 *
	 * @param string $public_id Public ID.
	 * @param int    $site_id Site ID.
	 * @return array<string,mixed>|null
	 */
	public function find_by_public_id_for_site( $public_id, $site_id ) {
		$this->lookup = array(
			'public_id' => $public_id,
			'site_id'   => $site_id,
		);

		return $this->stored;
	}

	/**
	 * Stores client report.
	 *
	 * @param int                 $action_id Action ID.
	 * @param array<string,mixed> $client_action Client action.
	 * @param string|null         $now Now.
	 * @return bool
	 */
	public function mark_client_report( $action_id, array $client_action, $now = null ) {
		$this->client_report = array(
			'action_id'     => $action_id,
			'client_action' => $client_action,
			'now'           => $now,
		);

		return true;
	}
}
