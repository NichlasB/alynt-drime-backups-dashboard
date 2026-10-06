<?php
/**
 * Remote action reconciliation guard tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/remote-action-reconciler-test-harness.php';

/**
 * Tests dashboard action reconciliation guard behavior.
 */
class RemoteActionReconcilerGuardsTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Reconciler_Test_Fixtures;

	/**
	 * Mismatched action types are not reconciled.
	 *
	 * @return void
	 */
	public function test_skips_mismatched_action_type() {
		$repository         = new Alynt_Drime_Backups_Dashboard_Test_Reconciler_Action_Repository();
		$repository->stored = array(
			'id'          => 99,
			'public_id'   => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
			'action_type' => 'restore_now',
		);
		$reconciler         = new Alynt_Drime_Backups_Dashboard_Remote_Action_Reconciler( $repository );
		$result             = $reconciler->reconcile_site_payload( 7, $this->payload(), '2026-08-20 12:05:00' );

		$this->assertSame( 0, $result['matched'] );
		$this->assertSame( array(), $repository->client_report );
	}

	/**
	 * Sanitizer fallback states do not reconcile ambiguous client evidence.
	 *
	 * @return void
	 */
	public function test_skips_sanitizer_fallback_client_state() {
		$repository         = new Alynt_Drime_Backups_Dashboard_Test_Reconciler_Action_Repository();
		$repository->stored = array(
			'id'          => 99,
			'public_id'   => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
			'action_type' => 'scan_upload_now',
		);
		$payload            = $this->payload();
		$payload['remote_actions']['last_action']['state'] = 'run arbitrary command';
		$reconciler         = new Alynt_Drime_Backups_Dashboard_Remote_Action_Reconciler( $repository );
		$result             = $reconciler->reconcile_site_payload( 7, $payload, '2026-08-20 12:05:00' );

		$this->assertSame( 0, $result['matched'] );
		$this->assertSame( array(), $repository->client_report );
	}

	/**
	 * Terminal dashboard evidence is not downgraded by later progress-like evidence.
	 *
	 * @return void
	 */
	public function test_skips_terminal_to_progress_downgrade() {
		$repository         = new Alynt_Drime_Backups_Dashboard_Test_Reconciler_Action_Repository();
		$repository->stored = array(
			'id'          => 99,
			'public_id'   => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
			'action_type' => 'scan_upload_now',
			'state'       => 'succeeded',
		);
		$payload            = $this->payload();
		$payload['remote_actions']['last_action']['state'] = 'running';
		$reconciler         = new Alynt_Drime_Backups_Dashboard_Remote_Action_Reconciler( $repository );
		$result             = $reconciler->reconcile_site_payload( 7, $payload, '2026-08-20 12:05:00' );

		$this->assertSame( 0, $result['matched'] );
		$this->assertSame( array(), $repository->client_report );
	}

	/**
	 * Older client evidence does not replace a newer reconciled report.
	 *
	 * @return void
	 */
	public function test_skips_older_client_report() {
		$repository         = new Alynt_Drime_Backups_Dashboard_Test_Reconciler_Action_Repository();
		$repository->stored = array(
			'id'                => 99,
			'public_id'         => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
			'action_type'       => 'scan_upload_now',
			'state'             => 'running',
			'client_updated_at' => '2026-08-20 12:04:00',
		);
		$payload            = $this->payload();
		$payload['remote_actions']['last_action']['updated_at'] = '2026-08-20T12:03:00+00:00';
		$reconciler         = new Alynt_Drime_Backups_Dashboard_Remote_Action_Reconciler( $repository );
		$result             = $reconciler->reconcile_site_payload( 7, $payload, '2026-08-20 12:05:00' );

		$this->assertSame( 0, $result['matched'] );
		$this->assertSame( array(), $repository->client_report );
	}
}
