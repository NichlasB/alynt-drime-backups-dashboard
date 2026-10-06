<?php
/**
 * Admin schedule rollback-preview rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-schedule-management-test-harness.php';

/**
 * Tests read-only schedule rollback-preview rendering.
 */
class AdminPageScheduleRollbackPreviewTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Schedule_Management_Test_Fixtures;

	/**
	 * Rollback-preview readiness distinguishes support from ready metadata.
	 *
	 * @return void
	 */
	public function test_schedule_rollback_preview_readiness_waits_for_apply_metadata() {
		$harness                 = new Alynt_Drime_Backups_Dashboard_Schedule_Management_Test_Harness();
		$harness->remote_actions = new Alynt_Drime_Backups_Dashboard_Schedule_Management_Test_Actions();
		$html                    = $harness->panel_html( $this->payload( true, true ) );

		$this->assertStringContainsString( 'adbd-status-pill is-waiting', $html );
		$this->assertStringContainsString( '>Waiting</span> Supported by the client; waiting for a successful Schedule Apply with rollback metadata.', $html );
		$this->assertStringNotContainsString( 'Preview Rollback', $html );
		$this->assertStringNotContainsString( 'schedule_rollback_preview_confirm', $html );
	}

	/**
	 * Rollback-preview readiness shows ready only for fresh successful apply metadata.
	 *
	 * @return void
	 */
	public function test_schedule_rollback_preview_readiness_shows_ready_with_apply_metadata() {
		$harness                 = new Alynt_Drime_Backups_Dashboard_Schedule_Management_Test_Harness();
		$harness->remote_actions = new Alynt_Drime_Backups_Dashboard_Schedule_Management_Test_Actions();
		$history                 = array(
			array(
				'public_id'             => '33333333-3333-4333-8333-333333333333',
				'action_type'           => 'schedule_apply',
				'state'                 => 'succeeded',
				'redacted_context_json' => wp_json_encode(
					array(
						'schedule_apply' => array(
							'schedule_id'       => 'alynt_scan_upload',
							'rollback_metadata' => array(
								'captured' => true,
							),
						),
					)
				),
			),
		);
		$html                    = $harness->panel_html( $this->payload( true, true ), $history );

		$this->assertStringContainsString( 'adbd-status-pill is-ready', $html );
		$this->assertStringContainsString( '>Ready</span> Ready for non-mutating rollback preview from the latest successful Schedule Apply.', $html );
		$this->assertStringContainsString( 'Preview Rollback', $html );
		$this->assertStringContainsString( 'schedule_rollback_preview_confirm', $html );
		$this->assertStringContainsString( 'only previews rollback readiness and does not execute a rollback', $html );
		$this->assertStringNotContainsString( 'schedule_rollback&quot;', $html );
	}

	/**
	 * Schedule panel keeps the latest rollback-preview proof visible after preview support is disabled.
	 *
	 * @return void
	 */
	public function test_schedule_panel_renders_latest_rollback_preview_evidence_when_preview_is_hidden() {
		$harness                 = new Alynt_Drime_Backups_Dashboard_Schedule_Management_Test_Harness();
		$harness->remote_actions = new Alynt_Drime_Backups_Dashboard_Schedule_Management_Test_Actions();
		$history                 = array(
			array(
				'public_id'             => '44444444-4444-4444-8444-444444444444',
				'action_type'           => 'schedule_rollback_preview',
				'state'                 => 'succeeded',
				'redacted_context_json' => wp_json_encode(
					array(
						'schedule_rollback_preview' => array(
							'schedule_id'        => 'alynt_scan_upload',
							'current_cadence'    => 'every_30_minutes',
							'rollback_cadence'   => 'every_15_minutes',
							'would_change'       => true,
							'rollback_supported' => false,
						),
					)
				),
			),
		);
		$html                    = $harness->panel_html( $this->payload( true, false ), $history );

		$this->assertStringContainsString( 'Latest rollback preview', $html );
		$this->assertStringContainsString( '>Previewed</span> Latest proof: rollback preview would restore every 30 minutes → every 15 minutes. No schedule was changed; rollback execution remains unavailable.', $html );
		$this->assertStringContainsString( '>Hidden</span> Hidden until the latest client report advertises rollback-preview support.', $html );
		$this->assertStringNotContainsString( 'Preview Rollback', $html );
		$this->assertStringNotContainsString( 'schedule_rollback_preview_confirm', $html );
	}
}
