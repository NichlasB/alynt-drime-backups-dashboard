<?php
/**
 * Admin schedule-management preview rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/includes/class-remote-action-capabilities.php';
require_once dirname( __DIR__ ) . '/includes/class-remote-action-repository.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-admin-page-time-formatters.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-admin-page-backup-source-evidence-helpers.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-admin-page-backup-source-compact-helpers.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-admin-page-backup-source-operator-helpers.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-admin-page-backup-source-evidence.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-admin-page-basic-detail-helpers.php';

/**
 * Tests read-only schedule-management preview rendering.
 */
class AdminPageScheduleManagementTest extends TestCase {
	/**
	 * Site Detail renders preview-only schedule capability without mutation controls.
	 *
	 * @return void
	 */
	public function test_schedule_preview_panel_renders_without_apply_controls() {
		$harness = new Alynt_Drime_Backups_Dashboard_Schedule_Management_Test_Harness();
		$html    = $harness->panel_html( $this->payload() );

		$this->assertStringContainsString( 'Schedule Management', $html );
		$this->assertStringContainsString( 'Preview only', $html );
		$this->assertStringContainsString( 'Alynt scan/upload', $html );
		$this->assertStringContainsString( 'every 15 minutes', $html );
		$this->assertStringContainsString( '15 minutes', $html );
		$this->assertStringContainsString( 'Not enabled on the client', $html );
		$this->assertStringContainsString( 'rollback metadata is evidence only and no rollback action exists', $html );
		$this->assertStringContainsString( 'Rollback remains unavailable in this release', $html );
		$this->assertStringContainsString( 'Preview Schedule Change', $html );
		$this->assertStringContainsString( 'every 30 minutes', $html );
		$this->assertStringContainsString( '<form', $html );
		$this->assertStringNotContainsString( 'schedule_apply', $html );
		$this->assertStringNotContainsString( 'schedule_rollback', $html );
	}

	/**
	 * Apply-capable schedule management shows apply only for a fresh preview.
	 *
	 * @return void
	 */
	public function test_schedule_apply_form_requires_fresh_preview() {
		$harness                 = new Alynt_Drime_Backups_Dashboard_Schedule_Management_Test_Harness();
		$harness->remote_actions = new Alynt_Drime_Backups_Dashboard_Schedule_Management_Test_Actions();
		$html                    = $harness->panel_html( $this->payload( true ) );

		$this->assertStringContainsString( 'Apply available after preview', $html );
		$this->assertStringContainsString( 'Apply Previewed Schedule Change', $html );
		$this->assertStringContainsString( 'schedule_apply_confirm', $html );
		$this->assertStringContainsString( 'from every 15 minutes to every 30 minutes', $html );
		$this->assertStringContainsString( 'future scan scheduling for the Alynt uploader only', $html );
		$this->assertStringContainsString( 'upload worker may keep its own cadence', $html );
		$this->assertStringContainsString( 'rollback is not available in this release', $html );
		$this->assertStringContainsString( 'rollback is unavailable', $html );
		$this->assertStringNotContainsString( 'future scan/upload timing only', $html );
		$this->assertStringNotContainsString( 'schedule_rollback', $html );
	}

	/**
	 * Sites list can show a compact schedule-preview hint.
	 *
	 * @return void
	 */
	public function test_schedule_preview_row_hint_is_compact() {
		$harness = new Alynt_Drime_Backups_Dashboard_Schedule_Management_Test_Harness();
		$html    = $harness->row_hint_html( $this->payload() );

		$this->assertStringContainsString( 'Schedule: preview only', $html );
		$this->assertStringContainsString( 'Alynt scan/upload every 15 minutes', $html );
		$this->assertStringNotContainsString( '<form', $html );
	}

	/**
	 * Sites list distinguishes apply-capable schedule hints without rendering controls.
	 *
	 * @return void
	 */
	public function test_schedule_apply_capable_row_hint_is_display_only() {
		$harness = new Alynt_Drime_Backups_Dashboard_Schedule_Management_Test_Harness();
		$html    = $harness->row_hint_html( $this->payload( true ) );

		$this->assertStringContainsString( 'Schedule: apply gated', $html );
		$this->assertStringContainsString( 'Alynt scan/upload every 15 minutes', $html );
		$this->assertStringNotContainsString( '<form', $html );
		$this->assertStringNotContainsString( 'Preview Schedule Change', $html );
		$this->assertStringNotContainsString( 'Apply Previewed Schedule Change', $html );
	}

	/**
	 * Missing capability renders a clear no-data state.
	 *
	 * @return void
	 */
	public function test_missing_schedule_preview_reports_not_reported() {
		$harness = new Alynt_Drime_Backups_Dashboard_Schedule_Management_Test_Harness();
		$html    = $harness->panel_html( array() );

		$this->assertStringContainsString( 'Not reported yet', $html );
		$this->assertStringNotContainsString( '<form', $html );
	}

	/**
	 * Creates a sanitized snapshot payload with preview-only schedule capability.
	 *
	 * @return array<string,mixed>
	 */
	private function payload( $apply_supported = false ) {
		return array(
			'remote_actions' => array(
				'protocol_version'     => 2,
				'enabled'              => true,
				'sodium_available'     => true,
				'allowed_actions'      => $apply_supported ? array( 'scan_upload_now', 'schedule_preview', 'schedule_apply' ) : array( 'scan_upload_now', 'schedule_preview' ),
				'schedule_management'  => array(
					'protocol_version'   => 2,
					'capability_version' => 1,
					'enabled'            => true,
					'preview_only'       => ! $apply_supported,
					'apply_supported'    => $apply_supported,
					'rollback_supported' => false,
					'schedules'          => array(
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
	 * @return string
	 */
	public function panel_html( array $payload ) {
		ob_start();
		$this->render_schedule_management_panel(
			array(
				'decoded_payload' => $payload,
			),
			array(
				'id' => 9,
			)
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

/**
 * Fake action repository for schedule apply rendering tests.
 */
class Alynt_Drime_Backups_Dashboard_Schedule_Management_Test_Actions extends Alynt_Drime_Backups_Dashboard_Remote_Action_Repository {
	/**
	 * Recent action rows.
	 *
	 * @param int $site_id Site ID.
	 * @param int $limit Limit.
	 * @return array<int,array<string,mixed>>
	 */
	public function recent_for_site( $site_id, $limit = 10 ) {
		unset( $site_id, $limit );

		return array(
			array(
				'public_id'   => '22222222-2222-4222-8222-222222222222',
				'action_type' => 'schedule_preview',
				'state'       => 'succeeded',
			),
		);
	}

	/**
	 * Fresh preview response.
	 *
	 * @param int                 $site_id Site ID.
	 * @param string              $preview_public_id Preview ID.
	 * @param array<string,mixed> $capabilities Capabilities.
	 * @param string|null         $now Now.
	 * @return array<string,mixed>
	 */
	public function fresh_schedule_preview_for_apply( $site_id, $preview_public_id, array $capabilities, $now = null ) {
		unset( $site_id, $capabilities, $now );

		return array(
			'preview_action_id'   => $preview_public_id,
			'preview_fingerprint' => str_repeat( 'a', 64 ),
			'schedule_id'         => 'alynt_scan_upload',
			'current_cadence'     => 'every_15_minutes',
			'proposed_cadence'    => 'every_30_minutes',
			'capability_version'  => 1,
		);
	}
}
