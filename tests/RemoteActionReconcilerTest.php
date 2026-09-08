<?php
/**
 * Remote action reconciliation tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

/**
 * Fake action repository for reconciliation tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Reconciler_Action_Repository extends Alynt_Drime_Backups_Dashboard_Remote_Action_Repository {
	/**
	 * Stored row.
	 *
	 * @var array<string,mixed>|null
	 */
	public $stored = null;

	/**
	 * Last lookup.
	 *
	 * @var array<string,mixed>
	 */
	public $lookup = array();

	/**
	 * Last client report.
	 *
	 * @var array<string,mixed>
	 */
	public $client_report = array();

	/**
	 * Stale update count.
	 *
	 * @var int
	 */
	public $stale = 0;

	/**
	 * Constructor.
	 */
	public function __construct() {}

	/**
	 * Finds by public ID and site.
	 *
	 * @param string $public_id Public ID.
	 * @param int    $site_id Site ID.
	 * @return array<string,mixed>|null
	 */
	public function find_by_public_id_for_site( $public_id, $site_id ) {
		$this->lookup = array(
			'public_id' => $public_id,
			'site_id'   => $site_id,
		);

		return $this->stored;
	}

	/**
	 * Stores client report.
	 *
	 * @param int                 $action_id Action ID.
	 * @param array<string,mixed> $client_action Client action.
	 * @param string|null         $now Now.
	 * @return bool
	 */
	public function mark_client_report( $action_id, array $client_action, $now = null ) {
		$this->client_report = array(
			'action_id'     => $action_id,
			'client_action' => $client_action,
			'now'           => $now,
		);

		return true;
	}

	/**
	 * Marks stale actions.
	 *
	 * @param int         $site_id Site ID.
	 * @param string|null $now Now.
	 * @return int
	 */
	public function mark_unconfirmed_actions_stale_for_site( $site_id, $now = null ) {
		unset( $site_id, $now );

		return $this->stale;
	}
}

/**
 * Tests dashboard action reconciliation from status payload evidence.
 */
class RemoteActionReconcilerTest extends TestCase {
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

	/**
	 * Missing client last-action still runs stale maintenance.
	 *
	 * @return void
	 */
	public function test_missing_last_action_still_marks_stale_actions() {
		$repository        = new Alynt_Drime_Backups_Dashboard_Test_Reconciler_Action_Repository();
		$repository->stale = 2;
		$reconciler        = new Alynt_Drime_Backups_Dashboard_Remote_Action_Reconciler( $repository );
		$result            = $reconciler->reconcile_site_payload(
			7,
			array(
				'remote_actions' => array(
					'protocol_version' => 2,
					'enabled'          => true,
				),
			)
		);

		$this->assertSame( 0, $result['matched'] );
		$this->assertSame( 2, $result['stale'] );
		$this->assertSame( array(), $repository->lookup );
	}

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
