<?php
/**
 * Admin schedule-management preview rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/includes/class-remote-action-capabilities.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-admin-page-time-formatters.php';
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

		$this->assertStringContainsString( 'Schedule Management Preview', $html );
		$this->assertStringContainsString( 'Preview only', $html );
		$this->assertStringContainsString( 'Alynt scan/upload', $html );
		$this->assertStringContainsString( 'every 15 minutes', $html );
		$this->assertStringContainsString( '15 minutes', $html );
		$this->assertStringContainsString( 'Not available in this version', $html );
		$this->assertStringNotContainsString( 'schedule_apply', $html );
		$this->assertStringNotContainsString( 'schedule_rollback', $html );
		$this->assertStringNotContainsString( '<form', $html );
	}

	/**
	 * Sites list can show a compact schedule-preview hint.
	 *
	 * @return void
	 */
	public function test_schedule_preview_row_hint_is_compact() {
		$harness = new Alynt_Drime_Backups_Dashboard_Schedule_Management_Test_Harness();
		$html    = $harness->row_hint_html( $this->payload() );

		$this->assertStringContainsString( 'Schedule preview: Alynt scan/upload every 15 minutes', $html );
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
	private function payload() {
		return array(
			'remote_actions' => array(
				'protocol_version'     => 2,
				'enabled'              => true,
				'schedule_management'  => array(
					'protocol_version'   => 2,
					'capability_version' => 1,
					'enabled'            => true,
					'preview_only'       => true,
					'apply_supported'    => false,
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
							'supported_cadences'             => array( 'every_15_minutes' ),
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
