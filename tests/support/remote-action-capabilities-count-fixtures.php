<?php
/**
 * Remote action capability count fixture builders.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared count fixture builders for remote action capability tests.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Count_Fixtures {
	/**
	 * Builds representative latest-action counts.
	 *
	 * @return array<string,int>
	 */
	private function valid_remote_action_last_action_counts() {
		return array(
			'found'            => 3,
			'queued'           => 1,
			'already_known'    => 2,
			'upload_attempted' => 1,
			'failed'           => -3,
		);
	}
}
