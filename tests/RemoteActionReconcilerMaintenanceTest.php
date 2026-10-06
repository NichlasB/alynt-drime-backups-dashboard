<?php
/**
 * Remote action reconciliation maintenance tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/remote-action-reconciler-test-harness.php';

/**
 * Tests dashboard action reconciliation maintenance behavior.
 */
class RemoteActionReconcilerMaintenanceTest extends TestCase {
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
}
