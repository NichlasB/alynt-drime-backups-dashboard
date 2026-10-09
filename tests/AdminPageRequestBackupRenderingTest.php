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

	/**
	 * Builds a request-backup detail site row fixture.
	 *
	 * @param array<string,mixed> $overrides Site row overrides.
	 * @return array<string,mixed>
	 */
	private function request_backup_site( array $overrides = array() ) {
		return array_merge(
			array(
				'id'                            => 7,
				'enrollment_status'             => 'active',
				'polling_key_id'                => 'key-id',
				'has_polling_secret'            => '1',
				'action_key_id'                 => 'ak_test',
				'action_private_key_ciphertext' => 'ciphertext',
			),
			$overrides
		);
	}

	/**
	 * Builds a request-backup remote-action snapshot fixture.
	 *
	 * @param array<string,mixed> $remote_action_overrides Remote-action overrides.
	 * @return array<string,array<string,mixed>>
	 */
	private function request_backup_snapshot( array $remote_action_overrides = array() ) {
		return array(
			'decoded_payload' => array(
				'remote_actions' => array_merge(
					array(
						'protocol_version' => 2,
						'enabled'          => true,
						'allowed_actions'  => array( 'scan_upload_now' ),
						'sodium_available' => true,
					),
					$remote_action_overrides
				),
			),
		);
	}

	/**
	 * Builds a successful request-backup history row fixture.
	 *
	 * @return array<string,mixed>
	 */
	private function request_backup_history_row() {
		return array(
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
		);
	}

}
