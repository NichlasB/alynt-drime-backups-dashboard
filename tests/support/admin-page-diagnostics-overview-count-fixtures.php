<?php
/**
 * Admin diagnostics overview count fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared diagnostics overview count fixture builders.
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Overview_Count_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Overview_Aggregate_Count_Fixtures;

	/**
	 * Returns scheduler diagnostics for the overview fixture.
	 *
	 * @return array<string,mixed>
	 */
	private function overview_scheduler_diagnostics() {
		return array(
			'poll_hook'             => 'alynt_drime_backups_dashboard_poll_sites',
			'poll_schedule_state'   => 'scheduled',
			'poll_next_at'          => '2026-09-18 12:15:00',
			'poll_interval_seconds' => 900,
			'poll_batch_size'       => 5,
			'stale_after_seconds'   => 3600,
			'global_lock_active'    => false,
			'current_utc'           => '2026-09-18 12:00:00',
			'cleanup_hook'          => 'alynt_drime_backups_dashboard_cleanup_snapshots',
			'cleanup_state'         => 'scheduled',
			'cleanup_next_at'       => '2026-09-19 00:00:00',
			'retention_days'        => 30,
			'cleanup_batch_size'    => 100,
		);
	}

	/**
	 * Returns count diagnostics for the overview fixture.
	 *
	 * @return array<string,mixed>
	 */
	private function overview_count_diagnostics() {
		return array(
			'total_sites'         => 17,
			'polling_ready'       => 14,
			'not_polling'         => 3,
			'due_now'             => 6,
			'missing_credentials' => 0,
			'paused'              => 0,
			'with_failures'       => 0,
			'record_states'       => $this->overview_record_state_counts(),
			'attention_history'   => $this->overview_attention_history_counts(),
			'restore_readiness'   => $this->overview_restore_readiness_counts(),
			'local_removal'       => $this->overview_local_removal_counts(),
		);
	}

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
