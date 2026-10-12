<?php
/**
 * Admin request-backup unavailable rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-polling-state-rendering-bootstrap.php';
require_once __DIR__ . '/support/admin-page-request-backup-rendering-fixtures.php';

/**
 * Tests V2 request-backup unavailable-state UI rendering.
 */
class AdminPageRequestBackupUnavailableRenderingTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Request_Backup_Rendering_Fixtures;

	/**
	 * Missing V2.1 capability explains the client opt-in requirement.
	 *
	 * @return void
	 */
	public function test_request_backup_now_panel_explains_missing_capability() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$site    = $this->request_backup_site();
		$html    = $harness->request_backup_panel_html(
			$site,
			array(
				'decoded_payload' => array(),
			),
			array()
		);

		$this->assertStringContainsString( 'Not available yet', $html );
		$this->assertStringContainsString( 'does not advertise V2.1 scan/upload-now capability', $html );
		$this->assertStringContainsString( 'Generate V2 Opt-In Token', $html );
		$this->assertStringContainsString( 'aria-describedby="adbd-action-opt-in-generation-description"', $html );
		$this->assertStringContainsString( 'id="adbd-action-opt-in-generation-description"', $html );
		$this->assertStringContainsString( 'No V2 remote action requests are stored for this site yet.', $html );
	}

	/**
	 * Disabled V2 capability is shown as an opt-in requirement.
	 *
	 * @return void
	 */
	public function test_request_backup_now_panel_explains_disabled_v2_capability() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$site    = $this->request_backup_site();
		$html    = $harness->request_backup_panel_html(
			$site,
			$this->request_backup_snapshot(
				array(
					'enabled'         => false,
					'allowed_actions' => array(),
				)
			),
			array()
		);

		$this->assertStringContainsString( 'understands V2.1 remote actions', $html );
		$this->assertStringContainsString( 'Generate V2 Opt-In Token', $html );
	}
}
