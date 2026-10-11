<?php
/**
 * Retained-record rendering helpers for admin polling-state rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Exposes retained-record setup helpers for polling-state harness tests.
 */
trait Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Retained_Record_Methods {
	/**
	 * Configures counting repositories for retained-record removal rendering.
	 *
	 * @param int $snapshot_count Snapshot count.
	 * @param int $action_count Action history row count.
	 * @param int $non_terminal_action_count Non-terminal action row count.
	 * @return void
	 */
	private function configure_retained_record_counts( $snapshot_count, $action_count, $non_terminal_action_count ) {
		$this->snapshots      = new Alynt_Drime_Backups_Dashboard_Counting_Snapshot_Repository_Test_Double( $snapshot_count );
		$this->remote_actions = new Alynt_Drime_Backups_Dashboard_Counting_Remote_Action_Repository_Test_Double( $action_count, $non_terminal_action_count );
	}
}
