<?php
/**
 * Admin diagnostics overview rendering fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/admin-page-diagnostics-overview-aggregate-count-fixtures.php';
require_once __DIR__ . '/admin-page-diagnostics-overview-count-fixtures.php';

/**
 * Shared diagnostics overview rendering fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Overview_Test_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Overview_Count_Fixtures;

	/**
	 * Returns diagnostics data with record-state, restore, and local-removal aggregates.
	 *
	 * @return array<string,mixed>
	 */
	private function record_state_polling_summary_diagnostics() {
		return array(
			'scheduler' => $this->overview_scheduler_diagnostics(),
			'counts'    => $this->overview_count_diagnostics(),
			'summaries' => array(
				'attention_history' => 'repeated_attention_seen',
			),
			'recent'    => array(),
			'logging'   => array(),
			'support'   => array(),
		);
	}

	/**
	 * Returns diagnostics data for freshness notice rendering.
	 *
	 * @return array<string,mixed>
	 */
	private function freshness_notice_diagnostics() {
		return $this->scheduler_only_diagnostics(
			array(
				'current_utc'           => '2026-09-19 10:53:22',
				'poll_hook'             => 'alynt_drime_backups_dashboard_poll_sites',
				'poll_schedule_state'   => 'scheduled',
				'poll_next_at'          => '2026-09-19 11:06:29',
				'poll_interval_seconds' => 900,
				'poll_batch_size'       => 20,
				'stale_after_seconds'   => 3600,
			)
		);
	}

	/**
	 * Returns diagnostics data for dashboard runtime identity rendering.
	 *
	 * @return array<string,mixed>
	 */
	private function runtime_identity_diagnostics() {
		return $this->scheduler_only_diagnostics(
			array(
				'current_utc' => '2026-09-30 19:30:00',
			)
		);
	}

	/**
	 * Returns an otherwise-empty diagnostics payload with scheduler data.
	 *
	 * @param array<string,mixed> $scheduler Scheduler diagnostics.
	 * @return array<string,mixed>
	 */
	private function scheduler_only_diagnostics( $scheduler ) {
		return array(
			'scheduler' => $scheduler,
			'counts'    => array(),
			'recent'    => array(),
			'logging'   => array(),
			'support'   => array(),
		);
	}
}
