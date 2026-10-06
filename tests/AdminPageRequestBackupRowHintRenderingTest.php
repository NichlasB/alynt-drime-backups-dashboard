<?php
/**
 * Admin request-backup row hint rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-polling-state-rendering-bootstrap.php';

/**
 * Tests compact V2 request-backup row hint rendering.
 */
class AdminPageRequestBackupRowHintRenderingTest extends TestCase {
	/**
	 * Sites rows show a compact V2.1 eligibility hint from redacted capability evidence.
	 *
	 * @return void
	 */
	public function test_request_backup_now_row_hint_reports_capability_without_rendering_action_form() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$site    = array(
			'enrollment_status'  => 'active',
			'polling_key_id'     => 'key-id',
			'has_polling_secret' => '1',
		);
		$payload = array(
			'remote_actions' => array(
				'protocol_version' => 2,
				'enabled'          => true,
				'allowed_actions'  => array( 'scan_upload_now' ),
				'sodium_available' => true,
			),
		);
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
		$site    = array(
			'enrollment_status'  => 'active',
			'polling_key_id'     => 'key-id',
			'has_polling_secret' => '1',
		);
		$payload = array(
			'remote_actions' => array(
				'protocol_version' => 2,
				'enabled'          => true,
				'allowed_actions'  => array( 'scan_upload_now' ),
				'sodium_available' => true,
				'last_action'      => array(
					'action_id'   => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
					'action_type' => 'scan_upload_now',
					'state'       => 'rate_limited',
				),
			),
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
		$site    = array(
			'enrollment_status'  => 'active',
			'polling_key_id'     => 'key-id',
			'has_polling_secret' => '1',
		);
		$payload = array(
			'remote_actions' => array(
				'protocol_version' => 2,
				'enabled'          => false,
				'allowed_actions'  => array(),
				'sodium_available' => true,
			),
		);

		$this->assertStringContainsString( 'Request Backup: opt-in needed', $harness->request_backup_row_hint_html( $site, $payload ) );
	}
}
