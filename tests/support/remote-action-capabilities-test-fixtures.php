<?php
/**
 * Remote action capability test fixture builders.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/remote-action-capabilities-base-fixtures.php';
require_once __DIR__ . '/remote-action-capabilities-cleanup-fixtures.php';
require_once __DIR__ . '/remote-action-capabilities-rollback-fixtures.php';
require_once __DIR__ . '/remote-action-capabilities-schedule-apply-alias-fixtures.php';
require_once __DIR__ . '/remote-action-capabilities-schedule-alias-fixtures.php';
require_once __DIR__ . '/remote-action-capabilities-schedule-apply-fixtures.php';
require_once __DIR__ . '/remote-action-capabilities-schedule-fixtures.php';

/**
 * Shared fixture builders for remote action capability tests.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Test_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Base_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Cleanup_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Rollback_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Schedule_Fixtures;

	/**
	 * Creates a remote action capabilities helper.
	 *
	 * @return Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities
	 */
	private function remote_action_capabilities() {
		return new Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities();
	}
}
