<?php
/**
 * Admin request-backup rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-polling-state-rendering-bootstrap.php';

/**
 * Tests V2 request-backup UI rendering.
 */
class AdminPageRequestBackupRenderingTest extends TestCase {
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
				'protocol_version'   => 2,
				'enabled'            => true,
				'allowed_actions'    => array( 'scan_upload_now' ),
				'sodium_available'   => true,
			),
		);
		$html    = $harness->request_backup_row_hint_html( $site, $payload );

		$this->assertStringContainsString( 'Request Backup: capability reported', $html );
		$this->assertStringNotContainsString( '<form', $html );
	}

	/**
	 * The V2.1 detail panel renders a signed dispatch form when capability is present.
	 *
	 * @return void
	 */
	public function test_request_backup_now_panel_renders_dispatch_form_and_safe_history() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$site    = array(
			'id'                           => 7,
			'enrollment_status'  => 'active',
			'polling_key_id'     => 'key-id',
			'has_polling_secret' => '1',
			'action_key_id'                => 'ak_test',
			'action_private_key_ciphertext' => 'ciphertext',
		);
		$snapshot = array(
			'decoded_payload' => array(
				'remote_actions' => array(
					'protocol_version'   => 2,
					'enabled'            => true,
					'allowed_actions'    => array( 'scan_upload_now' ),
					'sodium_available'   => true,
				),
			),
		);
		$history  = array(
			array(
				'action_type'           => 'scan_upload_now',
				'state'                 => 'succeeded',
				'client_state'          => 'succeeded',
				'requested_at'          => '2026-08-20 12:00:00',
				'result_summary'        => 'Stored locally only.',
				'client_result_summary' => 'Scan completed safely.',
				'client_counts_json'    => wp_json_encode(
					array(
						'found'            => 2,
						'queued'           => 0,
						'already_known'    => 1,
						'upload_attempted' => 1,
						'failed'           => 0,
					)
				),
			),
		);
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
	 * Detail rows fail closed when a local signing key is missing.
	 *
	 * @return void
	 */
	public function test_request_backup_now_panel_requires_local_signing_key_when_detail_fields_are_loaded() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$site    = array(
			'id'                            => 7,
			'enrollment_status'             => 'active',
			'polling_key_id'                => 'key-id',
			'has_polling_secret'            => '1',
			'action_key_id'                 => '',
			'action_private_key_ciphertext' => '',
		);
		$snapshot = array(
			'decoded_payload' => array(
				'remote_actions' => array(
					'protocol_version' => 2,
					'enabled'          => true,
					'allowed_actions'  => array( 'scan_upload_now' ),
					'sodium_available' => true,
				),
			),
		);
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
		$site    = array(
			'enrollment_status'  => 'active',
			'polling_key_id'     => 'key-id',
			'has_polling_secret' => '1',
		);
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
		$site    = array(
			'enrollment_status'  => 'active',
			'polling_key_id'     => 'key-id',
			'has_polling_secret' => '1',
		);
		$payload = array(
			'remote_actions' => array(
				'protocol_version'   => 2,
				'enabled'            => false,
				'allowed_actions'    => array(),
				'sodium_available'   => true,
			),
		);
		$html    = $harness->request_backup_panel_html(
			$site,
			array(
				'decoded_payload' => $payload,
			),
			array()
		);

		$this->assertStringContainsString( 'understands V2.1 remote actions', $html );
		$this->assertStringContainsString( 'Generate V2 Opt-In Token', $html );
		$this->assertStringContainsString( 'Request Backup: opt-in needed', $harness->request_backup_row_hint_html( $site, $payload ) );
	}

}