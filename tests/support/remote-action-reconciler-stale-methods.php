<?php
/**
 * Fake stale-maintenance helpers for remote action reconciler tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Captures fake stale-maintenance behavior for reconciliation tests.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Reconciler_Stale_Methods {
	/**
	 * Stale update count.
	 *
	 * @var int
	 */
	public $stale = 0;

	/**
	 * Marks stale actions.
	 *
	 * @param int         $site_id Site ID.
	 * @param string|null $now Now.
	 * @return int
	 */
	public function mark_unconfirmed_actions_stale_for_site( $site_id, $now = null ) {
		unset( $site_id, $now );

		return $this->stale;
	}
}
