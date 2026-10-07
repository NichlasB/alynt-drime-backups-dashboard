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

/**
 * Shared schedule-management payload fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Schedule_Management_Test_Fixtures {
	/**
	 * Creates a sanitized snapshot payload with preview-only schedule capability.
	 *
	 * @param bool $apply_supported Apply supported.
	 * @param bool $rollback_preview_supported Rollback preview supported.
	 * @return array<string,mixed>
	 */
	private function payload( $apply_supported = false, $rollback_preview_supported = false ) {
		return array(
			'remote_actions' => array(
				'protocol_version'    => 2,
				'enabled'             => true,
				'sodium_available'    => true,
				'allowed_actions'     => $rollback_preview_supported ? array( 'scan_upload_now', 'schedule_preview', 'schedule_apply', 'schedule_rollback_preview' ) : ( $apply_supported ? array( 'scan_upload_now', 'schedule_preview', 'schedule_apply' ) : array( 'scan_upload_now', 'schedule_preview' ) ),
				'schedule_management' => array(
					'protocol_version'           => 2,
					'capability_version'         => 1,
					'enabled'                    => true,
					'preview_only'               => ! $apply_supported,
					'apply_supported'            => $apply_supported,
					'rollback_preview_supported' => $rollback_preview_supported,
					'rollback_supported'         => false,
					'schedules'                  => array(
						array(
							'schedule_id'                    => 'alynt_scan_upload',
							'label'                          => 'Alynt scan/upload',
							'owner'                          => 'alynt_uploader',
							'manageable'                     => true,
							'current_cadence'                => 'every_15_minutes',
							'current_interval_seconds'       => 900,
							'current_next_run_at'            => '2026-06-25T16:45:00+00:00',
							'supported_cadences'             => array( 'every_15_minutes', 'every_30_minutes', 'hourly' ),
							'minimum_interval_seconds'       => 900,
							'can_disable'                    => false,
							'requires_high_friction_disable' => true,
							'rollback_preview_supported'     => $rollback_preview_supported,
							'rollback_supported'             => false,
						),
					),
				),
			),
		);
	}
}

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
