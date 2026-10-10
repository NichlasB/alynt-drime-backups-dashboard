<?php
/**
 * Admin remote-action history detail rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-polling-state-rendering-bootstrap.php';

/**
 * Tests non-schedule remote-action history detail rendering.
 */
class AdminPageRemoteActionHistoryDetailRenderingTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Test_Remote_Action_Rendering_Fixtures;

	/**
	 * Cleanup-preview history renders support-safe category/count details.
	 *
	 * @return void
	 */
	public function test_remote_action_history_renders_cleanup_preview_details() {
		$harness  = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$site     = $this->remote_action_history_site();
		$snapshot = $this->remote_action_history_snapshot();
		$history  = array( $this->cleanup_preview_history_row() );
		$html     = $harness->request_backup_panel_html( $site, $snapshot, $history );

		$this->assertStringContainsString( 'Cleanup Preview', $html );
		$this->assertStringContainsString( 'Preview: 2 eligible temporary items; approx 2 KB', $html );
		$this->assertStringContainsString( 'Uploader temporary artifacts: 2 eligible; approx 2 KB; age older_than_24h; reason safe_local_uploader_owned_temp_artifacts', $html );
		$this->assertStringContainsString( 'Preview expires 2026-09-29 12:15 UTC', $html );
		$this->assertStringContainsString( 'Evidence only: no cleanup apply, delete, retention, restore, credential, or Drime action was requested', $html );
		$this->assertStringNotContainsString( 'C:\\', $html );
		$this->assertStringNotContainsString( '/home/', $html );
	}

	/**
	 * Short request-backup count details do not render an unnecessary disclosure.
	 *
	 * @return void
	 */
	public function test_remote_action_history_keeps_short_count_details_flat() {
		$harness  = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$site     = $this->remote_action_history_site();
		$snapshot = $this->remote_action_history_snapshot();
		$history  = array( $this->scan_upload_history_row() );
		$html     = $harness->request_backup_panel_html( $site, $snapshot, $history );

		$this->assertStringContainsString( 'Found 2; Queued 0; Known 1; Attempts 1; Failed 0', $html );
		$this->assertStringNotContainsString( 'adbd-history-detail-disclosure', $html );
	}
}
