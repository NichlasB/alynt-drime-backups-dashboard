<?php
/**
 * Poller remote-action reconciler test double.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Fake remote action reconciler for poller tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Poller_Remote_Action_Reconciler extends Alynt_Drime_Backups_Dashboard_Remote_Action_Reconciler {
	/**
	 * Calls.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	public $calls = array();

	/**
	 * Constructor.
	 */
	public function __construct() {}

	/**
	 * Reconciles payload.
	 *
	 * @param int                 $site_id Site ID.
	 * @param array<string,mixed> $payload Payload.
	 * @param string|null         $now Now.
	 * @return array<string,int>
	 */
	public function reconcile_site_payload( $site_id, array $payload, $now = null ) {
		unset( $now );

		$this->calls[] = array(
			'site_id' => $site_id,
			'payload' => $payload,
		);

		return array(
			'matched' => 0,
			'stale'   => 0,
		);
	}
}
