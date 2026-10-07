<?php
/**
 * Remote-action rendering methods for admin polling-state rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Exposes remote-action rendering helpers for polling-state harness tests.
 */
trait Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Remote_Action_Methods {
	/**
	 * Exposes V2.1 row hint markup.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @param array<string,mixed> $payload Payload.
	 * @return string
	 */
	public function request_backup_row_hint_html( array $site, array $payload ) {
		return $this->request_backup_now_row_hint( $site, $payload );
	}

	/**
	 * Exposes V2.1 detail-panel markup.
	 *
	 * @param array<string,mixed>            $site Site row.
	 * @param array<string,mixed>|null       $snapshot Snapshot row.
	 * @param array<int,array<string,mixed>> $history History rows.
	 * @return string
	 */
	public function request_backup_panel_html( array $site, $snapshot, array $history ) {
		ob_start();
		$this->render_request_backup_now_panel( $site, $snapshot, $history );
		return (string) ob_get_clean();
	}

	/**
	 * Exposes V2.3 schedule-management panel markup.
	 *
	 * @param array<string,mixed>                                         $site Site row.
	 * @param array<string,mixed>|null                                    $snapshot Snapshot row.
	 * @param array<int,array<string,mixed>>                              $history History rows.
	 * @param Alynt_Drime_Backups_Dashboard_Remote_Action_Repository|null $remote_actions Remote actions test double.
	 * @return string
	 */
	public function schedule_management_panel_html( array $site, $snapshot, array $history, $remote_actions = null ) {
		if ( $remote_actions instanceof Alynt_Drime_Backups_Dashboard_Remote_Action_Repository ) {
			$this->remote_actions = $remote_actions;
		}

		ob_start();
		$this->render_schedule_management_panel( $snapshot, $site, $history );
		return (string) ob_get_clean();
	}

	/**
	 * Exposes V2.4 cleanup-preview panel markup.
	 *
	 * @param array<string,mixed>      $site Site row.
	 * @param array<string,mixed>|null $snapshot Snapshot row.
	 * @return string
	 */
	public function cleanup_preview_panel_html( array $site, $snapshot ) {
		ob_start();
		$this->render_cleanup_preview_panel( $site, $snapshot );
		return (string) ob_get_clean();
	}
}
