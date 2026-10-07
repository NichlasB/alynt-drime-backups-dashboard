<?php
/**
 * Test harness for diagnostics support summaries.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Harness exposing support-summary action aggregates.
 */
class Alynt_Drime_Backups_Dashboard_Diagnostics_Support_Test_Harness {
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Support;

	/**
	 * Gets support-safe action aggregate output.
	 *
	 * @param array<string,mixed> $remote_actions Remote-action aggregate.
	 * @return array<string,mixed>
	 */
	public function support_action_summary( array $remote_actions ) {
		$support = $this->support_summary_from_diagnostics(
			array(
				'poll_schedule_state'   => 'scheduled',
				'cleanup_state'         => 'scheduled',
				'poll_interval_seconds' => 900,
				'poll_batch_size'       => 20,
				'retention_days'        => 30,
				'global_lock_active'    => false,
			),
			array(),
			array(),
			array(
				'settings' => array(),
				'summary'  => array(),
				'audit'    => array(
					'summary' => array(),
				),
			),
			1789843200,
			$remote_actions
		);

		return $support['actions'];
	}
}
