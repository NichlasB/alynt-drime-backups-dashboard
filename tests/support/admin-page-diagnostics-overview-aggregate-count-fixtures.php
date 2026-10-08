<?php
/**
 * Admin diagnostics overview aggregate count fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared diagnostics overview aggregate count fixture builders.
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Overview_Aggregate_Count_Fixtures {
	/**
	 * Returns restore-readiness counts for the overview fixture.
	 *
	 * @return array<string,int>
	 */
	private function overview_restore_readiness_counts() {
		return array(
			'reporting_sites'       => 14,
			'evidence_sites'        => 8,
			'incomplete_sites'      => 6,
			'stale_sites'           => 0,
			'incompatible_sites'    => 0,
			'unknown_sites'         => 0,
			'reported_candidates'   => 28,
			'complete_candidates'   => 8,
			'incomplete_candidates' => 20,
			'server_candidates'     => 14,
			'server_complete'       => 8,
			'server_incomplete'     => 6,
			'wpvivid_candidates'    => 14,
			'wpvivid_complete'      => 0,
			'wpvivid_incomplete'    => 14,
		);
	}

	/**
	 * Returns local-removal counts for the overview fixture.
	 *
	 * @return array<string,int>
	 */
	private function overview_local_removal_counts() {
		return array(
			'archived_records'         => 2,
			'ready_records'            => 1,
			'blocked_records'          => 1,
			'retained_snapshot_rows'   => 6,
			'retained_action_rows'     => 4,
			'non_terminal_action_rows' => 1,
		);
	}
}
