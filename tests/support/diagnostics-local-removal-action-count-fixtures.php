<?php
/**
 * Diagnostics local-removal action-count fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared local-removal action-count fixture builders.
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Local_Removal_Action_Count_Fixtures {
	/**
	 * Creates retained action counts keyed by site ID.
	 *
	 * @return array<int,int>
	 */
	private function local_removal_action_counts() {
		return array(
			1 => 3,
			2 => 2,
			3 => 4,
		);
	}

	/**
	 * Creates non-terminal action counts keyed by site ID.
	 *
	 * @return array<int,int>
	 */
	private function local_removal_non_terminal_action_counts() {
		return array(
			2 => 1,
		);
	}
}
