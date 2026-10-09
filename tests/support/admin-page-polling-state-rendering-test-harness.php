<?php
/**
 * Test support for admin polling-state rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/admin-page-polling-state-rendering-helper-stubs.php';
require_once __DIR__ . '/admin-page-polling-state-rendering-history-methods.php';

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
	use Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Local_Record_Methods;
	use Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Remote_Action_Methods;
	use Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_History_Methods;
	use Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Helper_Stubs {
		Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Helper_Stubs::decoded_snapshot_payload insteadof Alynt_Drime_Backups_Dashboard_Admin_Page_Basic_Detail_Helpers;
	}

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
	 * Exposes next-poll markup.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return string
	 */
	public function next_poll_line( array $site ) {
		return $this->next_poll_html( $site );
	}
}
