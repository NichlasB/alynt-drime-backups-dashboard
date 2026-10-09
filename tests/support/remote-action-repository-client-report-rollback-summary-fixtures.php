<?php
/**
 * Remote action repository rollback support-summary fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared rollback support-summary row fixtures for remote action repository tests.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Client_Report_Rollback_Summary_Fixtures {
	/**
	 * Returns aggregate support-summary row data for schedule rollback-readiness evidence.
	 *
	 * @return array<string,mixed>
	 */
	private function rollback_metadata_support_summary_row() {
		return array(
			'total'                      => 4,
			'client_reconciled'          => 3,
			'stale'                      => 1,
			'awaiting_confirmation'      => 1,
			'schedule_apply'             => 2,
			'schedule_rollback_preview'  => 1,
			'rollback_metadata_captured' => 1,
			'latest_updated_at'          => '2026-09-15 19:24:12',
		);
	}
}
