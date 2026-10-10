<?php
/**
 * Admin request-backup row hint rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-polling-state-rendering-bootstrap.php';
require_once __DIR__ . '/support/admin-page-request-backup-rendering-fixtures.php';

/**
 * Tests compact V2 request-backup row hint rendering.
 */
class AdminPageRequestBackupRowHintRenderingTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Request_Backup_Rendering_Fixtures;

	/**
	 * Sites rows show a compact V2.1 eligibility hint from redacted capability evidence.
	 *
	 * @return void
	 */
	public function test_request_backup_now_row_hint_reports_capability_without_rendering_action_form() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$site    = $this->request_backup_row_site();
		$payload = $this->request_backup_payload();
		$html    = $harness->request_backup_row_hint_html( $site, $payload );

		$this->assertStringContainsString( 'Request Backup: capability reported', $html );
		$this->assertStringNotContainsString( '<form', $html );
	}

	/**
	 * Sites rows include a compact latest-client-action hint from sanitized payload evidence.
	 *
	 * @return void
	 */
	public function test_request_backup_now_row_hint_includes_latest_client_action_state() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$site    = $this->request_backup_row_site();
		$payload = $this->request_backup_payload(
			array(
				'last_action'      => array(
					'action_id'   => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
					'action_type' => 'scan_upload_now',
					'state'       => 'rate_limited',
				),
			)
		);

		$this->assertStringContainsString( 'latest client action: Rate limited', $harness->request_backup_row_hint_html( $site, $payload ) );
	}

	/**
	 * Disabled V2 capability is shown as a compact opt-in requirement.
	 *
	 * @return void
	 */
	public function test_request_backup_now_row_hint_explains_disabled_v2_capability() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$site    = $this->request_backup_row_site();
		$payload = $this->request_backup_payload(
			array(
				'enabled'         => false,
				'allowed_actions' => array(),
			)
		);

		$this->assertStringContainsString( 'Request Backup: opt-in needed', $harness->request_backup_row_hint_html( $site, $payload ) );
	}
}
