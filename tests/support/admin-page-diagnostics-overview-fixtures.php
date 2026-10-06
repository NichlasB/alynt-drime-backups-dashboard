<?php
/**
 * Admin diagnostics overview rendering fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared diagnostics overview rendering fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Overview_Test_Fixtures {
	/**
	 * Returns diagnostics data with record-state, restore, and local-removal aggregates.
	 *
	 * @return array<string,mixed>
	 */
	private function record_state_polling_summary_diagnostics() {
		return array(
			'scheduler' => array(
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
			),
			'counts'    => array(
				'total_sites'         => 17,
				'polling_ready'       => 14,
				'not_polling'         => 3,
				'due_now'             => 6,
				'missing_credentials' => 0,
				'paused'              => 0,
				'with_failures'       => 0,
				'record_states'       => array(
					'active'              => 14,
					'awaiting_first_poll' => 0,
					'pending'             => 2,
					'revoked'             => 1,
					'archived'            => 0,
					'other'               => 0,
					'unknown'             => 0,
				),
				'attention_history'   => array(
					'records_with_history'         => 14,
					'recently_recovered_records'  => 2,
					'repeated_attention_records'  => 1,
					'recent_attention_transitions' => 3,
				),
				'restore_readiness'   => array(
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
				),
				'local_removal'       => array(
					'archived_records'         => 2,
					'ready_records'            => 1,
					'blocked_records'          => 1,
					'retained_snapshot_rows'   => 6,
					'retained_action_rows'     => 4,
					'non_terminal_action_rows' => 1,
				),
			),
			'summaries' => array(
				'attention_history' => 'repeated_attention_seen',
			),
			'recent'    => array(),
			'logging'   => array(),
			'support'   => array(),
		);
	}
}
