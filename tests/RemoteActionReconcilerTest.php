<?php
/**
 * Remote action reconciliation tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/remote-action-reconciler-test-harness.php';

/**
 * Tests dashboard action reconciliation from status payload evidence.
 */
class RemoteActionReconcilerTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Reconciler_Test_Fixtures;

	/**
	 * Reconciles matching client report.
	 *
	 * @return void
	 */
	public function test_reconciles_matching_client_last_action() {
		$repository         = new Alynt_Drime_Backups_Dashboard_Test_Reconciler_Action_Repository();
		$repository->stored = array(
			'id'          => 99,
			'public_id'   => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
			'action_type' => 'scan_upload_now',
		);
		$reconciler         = new Alynt_Drime_Backups_Dashboard_Remote_Action_Reconciler( $repository );
		$result             = $reconciler->reconcile_site_payload( 7, $this->payload(), '2026-08-20 12:05:00' );

		$this->assertSame( 1, $result['matched'] );
		$this->assertSame( 0, $result['stale'] );
		$this->assertSame( 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', $repository->lookup['public_id'] );
		$this->assertSame( 7, $repository->lookup['site_id'] );
		$this->assertSame( 99, $repository->client_report['action_id'] );
		$this->assertSame( 'succeeded', $repository->client_report['client_action']['state'] );
	}

	/**
	 * Reconciles current client code/summary aliases into dashboard result fields.
	 *
	 * @return void
	 */
	public function test_reconciles_client_code_summary_aliases() {
		$repository         = new Alynt_Drime_Backups_Dashboard_Test_Reconciler_Action_Repository();
		$repository->stored = array(
			'id'          => 21,
			'public_id'   => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
			'action_type' => 'schedule_preview',
		);
		$payload            = $this->payload();
		$payload['remote_actions']['last_action']['action_type'] = 'schedule_preview';
		unset( $payload['remote_actions']['last_action']['result_code'], $payload['remote_actions']['last_action']['result_summary'] );
		$payload['remote_actions']['last_action']['code']             = 'schedule_preview_ready';
		$payload['remote_actions']['last_action']['summary']          = 'Schedule preview is ready. No schedule was changed.';
		$payload['remote_actions']['last_action']['schedule_preview'] = array(
			'schedule_id'      => 'alynt_scan_upload',
			'current_cadence'  => 'every_15_minutes',
			'proposed_cadence' => 'every_30_minutes',
			'would_change'     => true,
		);
		$reconciler         = new Alynt_Drime_Backups_Dashboard_Remote_Action_Reconciler( $repository );
		$result             = $reconciler->reconcile_site_payload( 5, $payload, '2026-09-14 11:29:23' );

		$this->assertSame( 1, $result['matched'] );
		$this->assertSame( 21, $repository->client_report['action_id'] );
		$this->assertSame( 'succeeded', $repository->client_report['client_action']['state'] );
		$this->assertSame( 'schedule_preview_ready', $repository->client_report['client_action']['result_code'] );
		$this->assertSame( 'Schedule preview is ready. No schedule was changed.', $repository->client_report['client_action']['result_summary'] );
		$this->assertSame( 'every_30_minutes', $repository->client_report['client_action']['schedule_preview']['proposed_cadence'] );
	}

}
