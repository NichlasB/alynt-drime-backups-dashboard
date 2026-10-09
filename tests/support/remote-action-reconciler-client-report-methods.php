<?php
/**
 * Fake action repository client report methods for reconciler tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Lookup and client report methods for reconciler action repository tests.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Reconciler_Client_Report_Methods {
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
