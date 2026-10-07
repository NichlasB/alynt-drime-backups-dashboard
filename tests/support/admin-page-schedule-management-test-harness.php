<?php
/**
 * Admin schedule-management test harness.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once dirname( __DIR__, 2 ) . '/includes/class-remote-action-capabilities.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-remote-action-repository.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-time-formatters.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-backup-source-evidence-helpers.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-backup-source-compact-helpers.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-backup-source-operator-helpers.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-backup-source-evidence.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-basic-detail-helpers.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-schedule-rollback-preview-evidence.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-schedule-rollback-preview-helpers.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-schedule-management-action-forms.php';
require_once __DIR__ . '/admin-page-schedule-management-actions-double.php';
require_once __DIR__ . '/admin-page-schedule-management-payload-fixtures.php';

/**
 * Harness exposing private schedule preview helpers.
 */
class Alynt_Drime_Backups_Dashboard_Schedule_Management_Test_Harness {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Time_Formatters;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Backup_Source_Evidence;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Basic_Detail_Helpers;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Schedule_Management_Form_Helpers;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Schedule_Label_Helpers;

	/**
	 * Remote action repository.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Remote_Action_Repository|null
	 */
	public $remote_actions;

	/**
	 * Renders the schedule preview panel for tests.
	 *
	 * @param array<string,mixed> $payload Snapshot payload.
	 * @param array<int,array<string,mixed>> $remote_action_history Remote action history.
	 * @return string
	 */
	public function panel_html( array $payload, array $remote_action_history = array() ) {
		ob_start();
		$this->render_schedule_management_panel(
			array(
				'decoded_payload' => $payload,
			),
			array(
				'id' => 9,
			),
			$remote_action_history
		);
		return (string) ob_get_clean();
	}

	/**
	 * Renders the compact row hint for tests.
	 *
	 * @param array<string,mixed> $payload Snapshot payload.
	 * @return string
	 */
	public function row_hint_html( array $payload ) {
		return $this->schedule_management_row_hint( $payload );
	}
}
