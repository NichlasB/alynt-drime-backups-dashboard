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
}
