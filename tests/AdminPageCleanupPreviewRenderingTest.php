<?php
/**
 * Admin cleanup-preview rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-polling-state-rendering-bootstrap.php';

/**
 * Tests cleanup-preview panel rendering.
 */
class AdminPageCleanupPreviewRenderingTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Test_Remote_Action_Rendering_Fixtures;

	/**
	 * Cleanup-preview panel renders only when the latest capability explicitly supports it.
	 *
	 * @return void
	 */
	public function test_cleanup_preview_panel_renders_preview_only_control_when_supported() {
		$harness  = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$site     = $this->remote_action_history_site();
		$snapshot = $this->cleanup_preview_snapshot();
		$html     = $harness->cleanup_preview_panel_html( $site, $snapshot );

		$this->assertStringContainsString( 'Cleanup Preview', $html );
		$this->assertStringContainsString( 'Capability reported', $html );
		$this->assertStringContainsString( 'Preview Cleanup', $html );
		$this->assertStringContainsString( 'Cleanup apply is intentionally unavailable in this release', $html );
		$this->assertStringContainsString( 'preview-only evidence; cleanup apply remains unavailable', $html );
		$this->assertStringContainsString( 'value="cleanup_preview"', $html );
		$this->assertStringContainsString( 'alynt_drime_backups_dashboard_cleanup_preview', $html );
		$this->assertStringContainsString( 'no cleanup or delete action is requested', $html );
		$this->assertStringNotContainsString( 'Apply Cleanup', $html );
		$this->assertStringNotContainsString( 'Delete', $html );
	}

	/**
	 * Cleanup-preview panel renders latest sanitized preview evidence when reported.
	 *
	 * @return void
	 */
	public function test_cleanup_preview_panel_renders_latest_preview_evidence_summary() {
		$harness  = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$site     = $this->remote_action_history_site();
		$snapshot = $this->cleanup_preview_snapshot(
			array(
				'allowed_actions' => array( 'scan_upload_now', 'cleanup_preview', 'cleanup_apply' ),
			),
			array(
				'supported_categories' => array( 'uploader_temp_artifacts', 'server_backups' ),
			),
			array(
				'last_action' => array(
					'action_id'       => '44444444-4444-4444-8444-444444444444',
					'action_type'     => 'cleanup_preview',
					'state'           => 'succeeded',
					'code'            => 'cleanup_preview_ready',
					'summary'         => 'Cleanup preview is ready. Nothing was deleted.',
					'cleanup_preview' => array(
						'preview_action_id'    => '44444444-4444-4444-8444-444444444444',
						'preview_fingerprint'  => str_repeat( 'e', 64 ),
						'capability_version'   => 1,
						'scope'                => 'safe_local_uploader_owned',
						'preview_created_at'   => '2026-09-29T12:00:00+00:00',
						'expires_at'           => '2026-09-29T12:15:00+00:00',
						'total_eligible_count' => 2,
						'total_approx_bytes'   => 2048,
						'apply_supported'      => true,
						'categories'           => array(
							array(
								'category'       => 'uploader_temp_artifacts',
								'eligible_count' => 2,
								'approx_bytes'   => 2048,
								'age_band'       => 'older_than_24h',
								'reason_code'    => 'safe_local_uploader_owned_temp_artifacts',
							),
							array(
								'category'       => 'server_backups',
								'eligible_count' => 99,
								'approx_bytes'   => 999999,
							),
						),
					),
				),
			)
		);
		$html     = $harness->cleanup_preview_panel_html( $site, $snapshot );

		$this->assertStringContainsString( 'Latest preview evidence', $html );
		$this->assertStringContainsString( '2 eligible temporary items; approx 2 KB', $html );
		$this->assertStringContainsString( 'Uploader temporary artifacts', $html );
		$this->assertStringContainsString( '2 eligible; approx 2 KB; age older_than_24h; reason safe_local_uploader_owned_temp_artifacts', $html );
		$this->assertStringContainsString( 'Evidence only: no cleanup apply, delete, retention, restore, credential, or Drime action is available.', $html );
		$this->assertStringNotContainsString( 'server_backups', $html );
		$this->assertStringNotContainsString( '99 eligible', $html );
		$this->assertStringNotContainsString( 'Apply Cleanup', $html );
		$this->assertStringNotContainsString( '/home/', $html );
	}

	/**
	 * Cleanup-preview panel is hidden for clients that do not advertise support.
	 *
	 * @return void
	 */
	public function test_cleanup_preview_panel_is_hidden_without_capability() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$html    = $harness->cleanup_preview_panel_html( $this->remote_action_history_site(), $this->remote_action_history_snapshot() );

		$this->assertSame( '', $html );
	}
}
