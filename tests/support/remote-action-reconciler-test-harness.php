<?php
/**
 * Remote action reconciler test harness.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once dirname( __DIR__, 2 ) . '/includes/class-remote-action-repository.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-remote-action-reconciler.php';
require_once __DIR__ . '/remote-action-reconciler-action-repository-double.php';

/**
 * Shared remote action reconciler fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Reconciler_Test_Fixtures {
	/**
	 * Payload fixture.
	 *
	 * @return array<string,mixed>
	 */
	private function payload() {
		return array(
			'remote_actions' => array(
				'protocol_version' => 2,
				'enabled'          => true,
				'last_action'      => array(
					'action_id'      => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
					'action_type'    => 'scan_upload_now',
					'state'          => 'succeeded',
					'result_code'    => 'action_succeeded',
					'result_summary' => 'Scan completed safely.',
					'counts'         => array(
						'found'            => 2,
						'queued'           => 0,
						'already_known'    => 1,
						'upload_attempted' => 1,
						'failed'           => 0,
					),
				),
			),
		);
	}
}
