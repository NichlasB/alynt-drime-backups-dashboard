<?php
/**
 * Admin remote-action history rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-polling-state-rendering-bootstrap.php';

/**
 * Tests remote-action history rendering details.
 */
class AdminPageRemoteActionHistoryRenderingTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Test_Remote_Action_Rendering_Fixtures;

	/**
	 * Schedule action history shows operator-friendly schedule details.
	 *
	 * @return void
	 */
	public function test_remote_action_history_renders_schedule_apply_details() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$site    = array(
			'id'                            => 7,
			'enrollment_status'             => 'active',
			'polling_key_id'                => 'key-id',
			'has_polling_secret'            => '1',
			'action_key_id'                 => 'ak_test',
			'action_private_key_ciphertext' => 'ciphertext',
		);
		$snapshot = array(
			'decoded_payload' => array(
				'remote_actions' => array(
					'protocol_version'   => 2,
					'enabled'            => true,
					'allowed_actions'    => array( 'scan_upload_now', 'schedule_preview', 'schedule_apply' ),
					'sodium_available'   => true,
				),
			),
		);
		$history  = array(
			array(
				'action_type'           => 'schedule_apply',
				'state'                 => 'succeeded',
				'client_state'          => 'succeeded',
				'requested_at'          => '2026-09-15 18:23:43',
				'client_result_summary' => 'Schedule apply completed for Alynt scan/upload.',
				'redacted_context_json' => wp_json_encode(
					array(
						'schedule_apply' => array(
							'previous_cadence' => 'every_15_minutes',
							'applied_cadence'  => 'every_30_minutes',
							'new_next_run_at'  => '2026-09-15T18:53:55+00:00',
							'rollback_metadata' => array(
								'captured'  => true,
								'available' => false,
								'reason'    => 'schedule_rollback_runtime_not_implemented',
								'expires_at' => '2026-09-15T19:24:12+00:00',
							),
						),
					)
				),
			),
		);
		$html     = $harness->request_backup_panel_html( $site, $snapshot, $history );

		$this->assertStringContainsString( 'Schedule Apply', $html );
		$this->assertStringContainsString( 'every 15 minutes → every 30 minutes', $html );
		$this->assertStringContainsString( 'adbd-history-detail-summary', $html );
		$this->assertStringContainsString( 'adbd-history-detail-disclosure', $html );
		$this->assertStringContainsString( '<summary>Details</summary>', $html );
		$this->assertStringContainsString( 'Next run 2026-09-15 18:53 UTC', $html );
		$this->assertStringContainsString( 'Alynt uploader scan cadence only; upload worker cadence may remain separate', $html );
		$this->assertStringContainsString( 'Rollback metadata captured as evidence only; rollback action unavailable', $html );
		$this->assertStringContainsString( 'Reason schedule_rollback_runtime_not_implemented', $html );
		$this->assertStringContainsString( 'metadata expires 2026-09-15 19:24 UTC', $html );
	}

	/**
	 * Schedule rollback-preview history uses explicit preview-only operator wording.
	 *
	 * @return void
	 */
	public function test_remote_action_history_renders_schedule_rollback_preview_details() {
		$harness  = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$site     = $this->remote_action_history_site();
		$snapshot = $this->remote_action_history_snapshot();
		$history  = array(
			array(
				'action_type'           => 'schedule_rollback_preview',
				'state'                 => 'succeeded',
				'client_state'          => 'succeeded',
				'requested_at'          => '2026-09-15 19:00:00',
				'client_result_summary' => 'Schedule rollback preview is ready. No schedule was changed.',
				'redacted_context_json' => wp_json_encode(
					array(
						'schedule_rollback_preview' => array(
							'schedule_id'                   => 'alynt_scan_upload',
							'current_cadence'               => 'every_30_minutes',
							'rollback_cadence'              => 'every_15_minutes',
							'current_next_run_at'           => '2026-09-15T19:30:00+00:00',
							'rollback_next_run_estimate_at' => '2026-09-15T19:15:00+00:00',
							'would_change'                  => true,
						),
					)
				),
			),
		);
		$html     = $harness->request_backup_panel_html( $site, $snapshot, $history );

		$this->assertStringContainsString( 'Schedule Rollback Preview', $html );
		$this->assertStringContainsString( 'Rollback preview would restore: every 30 minutes → every 15 minutes', $html );
		$this->assertStringContainsString( 'Current next run 2026-09-15 19:30 UTC', $html );
		$this->assertStringContainsString( 'Rollback estimate next run 2026-09-15 19:15 UTC', $html );
		$this->assertStringContainsString( 'If applied in a future release, rollback would change the Alynt scan cadence; this preview did not change it', $html );
		$this->assertStringContainsString( 'Rollback execution remains unavailable', $html );
		$this->assertStringNotContainsString( 'Rollback completed', $html );
	}

	/**
	 * Cleanup-preview history renders support-safe category/count details.
	 *
	 * @return void
	 */
	public function test_remote_action_history_renders_cleanup_preview_details() {
		$harness  = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$site     = $this->remote_action_history_site();
		$snapshot = $this->remote_action_history_snapshot();
		$history  = array(
			array(
				'action_type'           => 'cleanup_preview',
				'state'                 => 'succeeded',
				'client_state'          => 'succeeded',
				'requested_at'          => '2026-09-29 12:00:00',
				'client_result_summary' => 'Cleanup preview is ready. Nothing was deleted.',
				'redacted_context_json' => wp_json_encode(
					array(
						'cleanup_preview' => array(
							'total_eligible_count' => 2,
							'total_approx_bytes'   => 2048,
							'expires_at'           => '2026-09-29T12:15:00+00:00',
							'categories'           => array(
								array(
									'category'       => 'uploader_temp_artifacts',
									'eligible_count' => 2,
									'approx_bytes'   => 2048,
									'age_band'       => 'older_than_24h',
									'reason_code'    => 'safe_local_uploader_owned_temp_artifacts',
								),
							),
						),
					)
				),
			),
		);
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
	 * Schedule history avoids presenting missing cadence evidence as a known transition.
	 *
	 * @return void
	 */
	public function test_remote_action_history_marks_pending_schedule_cadence_report() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$site    = array(
			'id'                            => 7,
			'enrollment_status'             => 'active',
			'polling_key_id'                => 'key-id',
			'has_polling_secret'            => '1',
			'action_key_id'                 => 'ak_test',
			'action_private_key_ciphertext' => 'ciphertext',
		);
		$snapshot = array(
			'decoded_payload' => array(
				'remote_actions' => array(
					'protocol_version' => 2,
					'enabled'          => true,
					'allowed_actions'  => array( 'scan_upload_now', 'schedule_preview', 'schedule_apply' ),
					'sodium_available' => true,
				),
			),
		);
		$history  = array(
			array(
				'action_type'           => 'schedule_preview',
				'state'                 => 'succeeded',
				'client_state'          => 'succeeded',
				'requested_at'          => '2026-09-15 18:10:00',
				'client_result_summary' => 'Schedule preview completed for Alynt scan/upload.',
				'redacted_context_json' => wp_json_encode(
					array(
						'schedule_preview' => array(
							'proposed_cadence' => 'every_15_minutes',
						),
					)
				),
			),
			array(
				'action_type'           => 'schedule_apply',
				'state'                 => 'succeeded',
				'client_state'          => 'succeeded',
				'requested_at'          => '2026-09-15 18:20:00',
				'client_result_summary' => 'Schedule apply completed for Alynt scan/upload.',
				'redacted_context_json' => wp_json_encode(
					array(
						'schedule_apply' => array(
							'applied_cadence' => 'every_15_minutes',
						),
					)
				),
			),
		);
		$html     = $harness->request_backup_panel_html( $site, $snapshot, $history );

		$this->assertStringContainsString( 'Preview target: every 15 minutes; current cadence pending client report', $html );
		$this->assertStringContainsString( 'Applied cadence: every 15 minutes; previous cadence pending client report', $html );
		$this->assertStringNotContainsString( 'Unknown → every 15 minutes', $html );
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
		$history  = array(
			array(
				'action_type'           => 'scan_upload_now',
				'state'                 => 'succeeded',
				'client_state'          => 'succeeded',
				'requested_at'          => '2026-09-15 18:00:00',
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

		$this->assertStringContainsString( 'Found 2; Queued 0; Known 1; Attempts 1; Failed 0', $html );
		$this->assertStringNotContainsString( 'adbd-history-detail-disclosure', $html );
	}

}