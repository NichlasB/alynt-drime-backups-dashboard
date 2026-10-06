<?php
/**
 * Test support for admin polling-state rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Harness exposing private polling-state rendering helpers.
 */
class Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Time_Formatters;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Local_Actions;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Archive_Actions;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Basic_Detail_Helpers;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Polling_Detail_Helpers;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Site_Detail_Local_Record_Panels;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Site_Detail;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Schedule_Management_Form_Helpers;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Schedule_Label_Helpers;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Request_Backup_Detail_Helpers;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Cleanup_Preview_Helpers;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Remote_Action_History_Helpers;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Status_History_Detail_Helpers;

	/**
	 * Remote action repository test double.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Remote_Action_Repository|null
	 */
	public $remote_actions;

	/**
	 * Snapshot repository test double.
	 *
	 * @var object|null
	 */
	public $snapshots;

	/**
	 * Exposes check-status action markup.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return string
	 */
	public function check_form_html( array $site ) {
		ob_start();
		$this->render_check_status_form( $site, 7, false );
		return (string) ob_get_clean();
	}

	/**
	 * Exposes scheduled polling pause/resume markup.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return string
	 */
	public function pause_form_html( array $site ) {
		ob_start();
		$this->render_polling_pause_form( $site, 7 );
		return (string) ob_get_clean();
	}

	/**
	 * Exposes scheduled polling control panel markup.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return string
	 */
	public function pause_panel_html( array $site ) {
		ob_start();
		$this->render_polling_pause_panel( $site );
		return (string) ob_get_clean();
	}

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
	 * Exposes attention/recovery history markup.
	 *
	 * @param array<int,array<string,mixed>> $history Snapshot history rows.
	 * @return string
	 */
	public function attention_recovery_history_html( array $history ) {
		ob_start();
		$this->render_attention_recovery_history( $history );
		return (string) ob_get_clean();
	}

	/**
	 * Exposes next-poll markup.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return string
	 */
	public function next_poll_line( array $site ) {
		return $this->next_poll_html( $site );
	}

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

	/**
	 * Minimal snapshot decoder needed by the included helper trait.
	 *
	 * @param array<string,mixed> $snapshot Snapshot row.
	 * @return array<string,mixed>
	 */
	private function decoded_snapshot_payload( array $snapshot ) {
		return isset( $snapshot['decoded_payload'] ) && is_array( $snapshot['decoded_payload'] ) ? $snapshot['decoded_payload'] : array();
	}

	/**
	 * Minimal backup source detail renderer needed by the included helper trait.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @return void
	 */
	private function render_backup_sources_detail( array $payload ) {
		unset( $payload );
	}
}

/**
 * Counts snapshot rows for local record rendering tests.
 */
class Alynt_Drime_Backups_Dashboard_Counting_Snapshot_Repository_Test_Double {
	/**
	 * Snapshot count.
	 *
	 * @var int
	 */
	private $count;

	/**
	 * Constructor.
	 *
	 * @param int $count Snapshot count.
	 */
	public function __construct( $count ) {
		$this->count = max( 0, (int) $count );
	}

	/**
	 * Counts rows for one site.
	 *
	 * @param int $site_id Site ID.
	 * @return int
	 */
	public function count_for_site( $site_id ) {
		unset( $site_id );

		return $this->count;
	}
}

/**
 * Counts remote-action rows for local record rendering tests.
 */
class Alynt_Drime_Backups_Dashboard_Counting_Remote_Action_Repository_Test_Double {
	/**
	 * Action row count.
	 *
	 * @var int
	 */
	private $count;

	/**
	 * Non-terminal action row count.
	 *
	 * @var int
	 */
	private $non_terminal_count;

	/**
	 * Constructor.
	 *
	 * @param int $count Action row count.
	 * @param int $non_terminal_count Non-terminal action row count.
	 */
	public function __construct( $count, $non_terminal_count ) {
		$this->count              = max( 0, (int) $count );
		$this->non_terminal_count = max( 0, (int) $non_terminal_count );
	}

	/**
	 * Counts rows for one site.
	 *
	 * @param int $site_id Site ID.
	 * @return int
	 */
	public function count_for_site( $site_id ) {
		unset( $site_id );

		return $this->count;
	}

	/**
	 * Counts non-terminal rows for one site.
	 *
	 * @param int $site_id Site ID.
	 * @return int
	 */
	public function count_non_terminal_for_site( $site_id ) {
		unset( $site_id );

		return $this->non_terminal_count;
	}
}
