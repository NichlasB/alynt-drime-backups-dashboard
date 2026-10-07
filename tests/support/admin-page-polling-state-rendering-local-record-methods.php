<?php
/**
 * Local-record rendering methods for admin polling-state rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Exposes local-record rendering helpers for polling-state harness tests.
 */
trait Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Local_Record_Methods {
	/**
	 * Exposes revoked-record guidance markup.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return string
	 */
	public function revoked_record_guidance_html( array $site ) {
		ob_start();
		$this->render_revoked_record_guidance( $site );
		return (string) ob_get_clean();
	}

	/**
	 * Exposes local archive-panel markup.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return string
	 */
	public function archive_record_panel_html( array $site ) {
		ob_start();
		$this->render_archive_record_panel( $site, isset( $site['id'] ) ? (int) $site['id'] : 7 );
		return (string) ob_get_clean();
	}

	/**
	 * Exposes local removal-preview markup.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @param int                 $snapshot_count Snapshot count.
	 * @param int                 $action_count Action history row count.
	 * @param int                 $non_terminal_action_count Non-terminal action row count.
	 * @return string
	 */
	public function retained_record_removal_preview_panel_html( array $site, $snapshot_count = 0, $action_count = 0, $non_terminal_action_count = 0 ) {
		$this->snapshots       = new Alynt_Drime_Backups_Dashboard_Counting_Snapshot_Repository_Test_Double( $snapshot_count );
		$this->remote_actions  = new Alynt_Drime_Backups_Dashboard_Counting_Remote_Action_Repository_Test_Double( $action_count, $non_terminal_action_count );
		ob_start();
		$this->render_retained_record_removal_preview_panel( $site, isset( $site['id'] ) ? (int) $site['id'] : 7 );
		return (string) ob_get_clean();
	}

	/**
	 * Exposes compact retained-record removal row hint markup.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @param int                 $snapshot_count Snapshot count.
	 * @param int                 $action_count Action history row count.
	 * @param int                 $non_terminal_action_count Non-terminal action row count.
	 * @return string
	 */
	public function retained_record_removal_row_hint_html( array $site, $snapshot_count = 0, $action_count = 0, $non_terminal_action_count = 0 ) {
		$this->snapshots      = new Alynt_Drime_Backups_Dashboard_Counting_Snapshot_Repository_Test_Double( $snapshot_count );
		$this->remote_actions = new Alynt_Drime_Backups_Dashboard_Counting_Remote_Action_Repository_Test_Double( $action_count, $non_terminal_action_count );

		return $this->retained_record_removal_row_hint( $site, isset( $site['id'] ) ? (int) $site['id'] : 7 );
	}
}
