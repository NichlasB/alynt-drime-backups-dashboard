<?php
/**
 * Remote action capability latest-action fixture builders.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared latest-action fixture builders for remote action capability tests.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Last_Action_Fixtures {
	/**
	 * Builds a representative valid latest-action summary.
	 *
	 * @return array<string,mixed>
	 */
	private function valid_remote_action_last_action_summary() {
		return array(
			'action_id'      => '11111111-1111-4111-8111-111111111111',
			'action_type'    => 'scan_upload_now',
			'state'          => 'succeeded',
			'requested_at'   => '2026-08-20T12:00:00+00:00',
			'completed_at'   => '2026-08-20T12:01:00+00:00',
			'result_code'    => 'ok',
			'result_summary' => str_repeat( 'A', 200 ),
			'counts'         => array(
				'found'            => 3,
				'queued'           => 1,
				'already_known'    => 2,
				'upload_attempted' => 1,
				'failed'           => -3,
			),
			'extra_field'    => 'ignored',
		);
	}
}
