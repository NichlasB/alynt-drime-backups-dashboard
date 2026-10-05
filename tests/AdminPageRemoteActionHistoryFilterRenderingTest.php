<?php
/**
 * Admin remote-action history filter rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-polling-state-rendering-bootstrap.php';

/**
 * Tests remote-action history filters.
 */
class AdminPageRemoteActionHistoryFilterRenderingTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Test_Remote_Action_Rendering_Fixtures;

	/**
	 * Remote action history filters keep Site Detail audit trails scannable.
	 *
	 * @return void
	 */
	public function test_remote_action_history_filters_by_action_type() {
		$harness  = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$site     = $this->remote_action_history_site();
		$snapshot = $this->remote_action_history_snapshot();
		$history  = $this->remote_action_history_rows();

		$_GET['adbd_history_action'] = 'schedule_apply';

		try {
			$html = $harness->request_backup_panel_html( $site, $snapshot, $history );
		} finally {
			unset( $_GET['adbd_history_action'] );
		}

		$this->assertStringContainsString( 'Filter History', $html );
		$this->assertStringContainsString( 'value="schedule_apply" selected="selected"', $html );
		$this->assertStringContainsString( 'Showing 1 of 3 remote action requests.', $html );
		$this->assertStringContainsString( 'Reset filters', $html );
		$this->assertStringContainsString( 'Schedule apply completed for Alynt scan/upload.', $html );
		$this->assertStringNotContainsString( 'Scan completed safely.', $html );
		$this->assertStringNotContainsString( 'Schedule preview completed for Alynt scan/upload.', $html );
	}

	/**
	 * Remote action history filters can narrow by dashboard state.
	 *
	 * @return void
	 */
	public function test_remote_action_history_filters_by_state() {
		$harness  = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$site     = $this->remote_action_history_site();
		$snapshot = $this->remote_action_history_snapshot();
		$history  = $this->remote_action_history_rows();

		$_GET['adbd_history_state'] = 'rate_limited';

		try {
			$html = $harness->request_backup_panel_html( $site, $snapshot, $history );
		} finally {
			unset( $_GET['adbd_history_state'] );
		}

		$this->assertStringContainsString( 'value="rate_limited" selected="selected"', $html );
		$this->assertStringContainsString( 'Showing 1 of 3 remote action requests.', $html );
		$this->assertStringContainsString( 'Rate limited by client.', $html );
		$this->assertStringNotContainsString( 'Schedule apply completed for Alynt scan/upload.', $html );
		$this->assertStringNotContainsString( 'Schedule preview completed for Alynt scan/upload.', $html );
	}

	/**
	 * Remote action history filters fail closed for unknown values and explain no-match results.
	 *
	 * @return void
	 */
	public function test_remote_action_history_filters_handle_invalid_and_empty_results() {
		$harness  = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$site     = $this->remote_action_history_site();
		$snapshot = $this->remote_action_history_snapshot();
		$history  = $this->remote_action_history_rows();

		$_GET['adbd_history_action'] = 'not_real';
		$_GET['adbd_history_state']  = 'failed';

		try {
			$html = $harness->request_backup_panel_html( $site, $snapshot, $history );
		} finally {
			unset( $_GET['adbd_history_action'], $_GET['adbd_history_state'] );
		}

		$this->assertStringContainsString( 'value="" selected="selected"', $html );
		$this->assertStringContainsString( 'value="failed" selected="selected"', $html );
		$this->assertStringContainsString( 'Showing 0 of 3 remote action requests.', $html );
		$this->assertStringContainsString( 'No remote action requests match the current filters.', $html );
		$this->assertStringNotContainsString( '<tbody>', $html );
	}

}