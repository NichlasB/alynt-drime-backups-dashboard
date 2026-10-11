<?php
/**
 * Admin diagnostics overview count fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/admin-page-diagnostics-overview-state-count-fixtures.php';
require_once __DIR__ . '/admin-page-diagnostics-overview-scheduler-fixtures.php';

/**
 * Shared diagnostics overview count fixture builders.
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Overview_Count_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Overview_Aggregate_Count_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Overview_Scheduler_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Overview_State_Count_Fixtures;

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
}
