<?php
/**
 * Admin schedule and cleanup rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-polling-state-rendering-bootstrap.php';

/**
 * Tests schedule rollback-preview and cleanup-preview rendering.
 */
class AdminPageScheduleCleanupRenderingTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Test_Remote_Action_Rendering_Fixtures;

	/**
	 * The schedule panel renders rollback preview only after successful apply evidence.
	 *
	 * @return void
	 */
	public function test_schedule_management_panel_renders_non_mutating_rollback_preview_form() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$history = array(
			array(
				'public_id'             => '33333333-3333-4333-8333-333333333333',
				'action_type'           => 'schedule_apply',
				'state'                 => 'succeeded',
				'redacted_context_json' => wp_json_encode(
					array(
						'schedule_apply' => array(
							'schedule_id'     => 'alynt_scan_upload',
							'previous_cadence' => 'every_15_minutes',
							'applied_cadence'  => 'every_30_minutes',
							'rollback_metadata' => array(
								'captured'                      => true,
								'schedule_id'                   => 'alynt_scan_upload',
								'rollback_metadata_fingerprint' => str_repeat( 'b', 64 ),
							),
						),
					)
				),
			),
		);
		$html    = $harness->schedule_management_panel_html(
			$this->remote_action_history_site(),
			$this->schedule_management_snapshot( true ),
			$history,
			new Alynt_Drime_Backups_Dashboard_Test_Admin_Actions()
		);

		$this->assertStringContainsString( 'Preview Rollback', $html );
		$this->assertStringContainsString( 'Rollback preview', $html );
		$this->assertStringContainsString( 'Ready for non-mutating rollback preview from the latest successful Schedule Apply.', $html );
		$this->assertStringContainsString( 'value="preview_schedule_rollback"', $html );
		$this->assertStringContainsString( 'name="source_apply_action_id" value="33333333-3333-4333-8333-333333333333"', $html );
		$this->assertStringContainsString( 'alynt_drime_backups_dashboard_preview_schedule_rollback', $html );
		$this->assertStringContainsString( 'does not execute a rollback or change any schedule', $html );
		$this->assertStringNotContainsString( 'value="schedule_rollback"', $html );
	}

	/**
	 * The schedule panel hides rollback preview unless the latest capability explicitly allows it.
	 *
	 * @return void
	 */
	public function test_schedule_management_panel_hides_rollback_preview_without_capability() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$html    = $harness->schedule_management_panel_html(
			$this->remote_action_history_site(),
			$this->schedule_management_snapshot( false ),
			array(),
			new Alynt_Drime_Backups_Dashboard_Test_Admin_Actions()
		);

		$this->assertStringContainsString( 'Rollback preview', $html );
		$this->assertStringContainsString( 'Hidden until the latest client report advertises rollback-preview support.', $html );
		$this->assertStringNotContainsString( 'Preview Rollback', $html );
		$this->assertStringNotContainsString( 'value="preview_schedule_rollback"', $html );
	}

	/**
	 * The schedule panel explains rollback-preview support while waiting for apply metadata.
	 *
	 * @return void
	 */
	public function test_schedule_management_panel_reports_rollback_preview_waiting_for_apply_metadata() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$html    = $harness->schedule_management_panel_html(
			$this->remote_action_history_site(),
			$this->schedule_management_snapshot( true ),
			array(),
			new Alynt_Drime_Backups_Dashboard_Test_Admin_Actions()
		);

		$this->assertStringContainsString( 'Rollback preview', $html );
		$this->assertStringContainsString( 'Supported by the client; waiting for a successful Schedule Apply with rollback metadata.', $html );
		$this->assertStringContainsString( 'Rollback preview becomes available after a successful Schedule Apply with unexpired rollback metadata and explicit client rollback-preview support.', $html );
		$this->assertStringNotContainsString( 'value="preview_schedule_rollback"', $html );
	}

}
