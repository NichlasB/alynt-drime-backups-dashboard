<?php
/**
 * Admin request-backup rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-polling-state-rendering-bootstrap.php';
require_once __DIR__ . '/support/admin-page-request-backup-rendering-fixtures.php';

/**
 * Tests V2 request-backup UI rendering.
 */
class AdminPageRequestBackupRenderingTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Request_Backup_Rendering_Fixtures;

	/**
	 * The V2.1 detail panel renders a signed dispatch form when capability is present.
	 *
	 * @return void
	 */
	public function test_request_backup_now_panel_renders_dispatch_form_and_safe_history() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$site    = $this->request_backup_site();
		$snapshot = $this->request_backup_snapshot();
		$history  = array( $this->request_backup_history_row() );
		$html     = $harness->request_backup_panel_html( $site, $snapshot, $history );

		$this->assertStringContainsString( 'Request Backup Now', $html );
		$this->assertStringContainsString( 'Capability reported', $html );
		$this->assertStringContainsString( '<form method="post"', $html );
		$this->assertStringContainsString( 'value="request_backup_now"', $html );
		$this->assertStringContainsString( 'value="7"', $html );
		$this->assertStringContainsString( 'aria-describedby="adbd-request-backup-now-description"', $html );
		$this->assertStringContainsString( 'id="adbd-request-backup-now-description"', $html );
		$this->assertStringContainsString( 'Dashboard', $html );
		$this->assertStringContainsString( 'Client report', $html );
		$this->assertStringContainsString( 'Details', $html );
		$this->assertStringContainsString( 'Succeeded', $html );
		$this->assertStringContainsString( 'Scan completed safely.', $html );
		$this->assertStringContainsString( 'Found 2; Queued 0; Known 1; Attempts 1; Failed 0', $html );
		$this->assertStringNotContainsString( 'private', strtolower( $html ) );
	}

	/**
	 * Detail rows fail closed when a local signing key is missing.
	 *
	 * @return void
	 */
	public function test_request_backup_now_panel_requires_local_signing_key_when_detail_fields_are_loaded() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$site    = $this->request_backup_site(
			array(
				'action_key_id'                 => '',
				'action_private_key_ciphertext' => '',
			)
		);
		$snapshot = $this->request_backup_snapshot();
		$html     = $harness->request_backup_panel_html( $site, $snapshot, array() );

		$this->assertStringContainsString( 'encrypted signing key', $html );
		$this->assertStringNotContainsString( 'value="request_backup_now"', $html );
	}

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
