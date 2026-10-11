<?php
/**
 * Admin diagnostics overview state count fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared diagnostics overview state count fixture builders.
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Overview_State_Count_Fixtures {
	/**
	 * Returns record-state counts for the overview fixture.
	 *
	 * @return array<string,int>
	 */
	private function overview_record_state_counts() {
		return array(
			'active'              => 14,
			'awaiting_first_poll' => 0,
			'pending'             => 2,
			'revoked'             => 1,
			'archived'            => 0,
			'other'               => 0,
			'unknown'             => 0,
		);
	}

	/**
	 * Returns attention-history counts for the overview fixture.
	 *
	 * @return array<string,int>
	 */
	private function overview_attention_history_counts() {
		return array(
			'records_with_history'         => 14,
			'recently_recovered_records'  => 2,
			'repeated_attention_records'  => 1,
			'recent_attention_transitions' => 3,
		);
	}
}
