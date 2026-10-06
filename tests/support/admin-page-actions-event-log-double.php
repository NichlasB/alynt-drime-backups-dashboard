<?php
/**
 * Fake event log for admin action tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Fake event log.
 */
class Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Event_Log {
	/**
	 * Audit calls.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	public $audit_calls = array();

	/**
	 * Records audit.
	 *
	 * @param string              $action Action.
	 * @param string              $outcome Outcome.
	 * @param array<string,mixed> $context Context.
	 * @return void
	 */
	public function audit_action( $action, $outcome, array $context = array() ) {
		$this->audit_calls[] = array(
			'action'  => $action,
			'outcome' => $outcome,
			'context' => $context,
		);
	}
}
