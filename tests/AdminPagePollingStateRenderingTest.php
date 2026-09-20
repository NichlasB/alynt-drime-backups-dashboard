<?php
/**
 * Admin polling-state rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

if ( ! function_exists( 'wp_nonce_field' ) ) {
	/**
	 * Minimal nonce-field shim.
	 *
	 * @param string $action Nonce action.
	 * @return void
	 */
	function wp_nonce_field( $action ) {
		echo '<input type="hidden" name="_wpnonce" value="' . esc_attr( $action ) . '">';
	}
}

if ( ! function_exists( 'esc_html_e' ) ) {
	/**
	 * Minimal esc_html_e shim.
	 *
	 * @param string $text Text.
	 * @param string $domain Domain.
	 * @return void
	 */
	function esc_html_e( $text, $domain = 'default' ) {
		echo esc_html__( $text, $domain );
	}
}

if ( ! function_exists( 'esc_attr_e' ) ) {
	/**
	 * Minimal esc_attr_e shim.
	 *
	 * @param string $text Text.
	 * @param string $domain Domain.
	 * @return void
	 */
	function esc_attr_e( $text, $domain = 'default' ) {
		echo esc_attr__( $text, $domain );
	}
}

require_once dirname( __DIR__ ) . '/includes/traits/trait-admin-page-time-formatters.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-admin-page-local-actions.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-admin-page-archive-actions.php';
require_once dirname( __DIR__ ) . '/includes/class-remote-action-capabilities.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-admin-page-basic-detail-helpers.php';

/**
 * Tests credential-aware Sites-row rendering.
 */
class AdminPagePollingStateRenderingTest extends TestCase {
	/**
	 * Sites-list rows use the redacted has_polling_secret flag to show manual checks.
	 *
	 * @return void
	 */
	public function test_sites_list_redacted_credential_flag_allows_manual_check() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$site    = array(
			'enrollment_status'  => 'active',
			'polling_key_id'     => 'key-id',
			'has_polling_secret' => '1',
			'next_poll_at'       => '2026-08-13 12:30:00',
		);

		$action_html = $harness->check_form_html( $site );
		$next_html   = $harness->next_poll_line( $site );

		$this->assertStringContainsString( 'Check Now', $action_html );
		$this->assertStringNotContainsString( 'Manual check unavailable', $action_html );
		$this->assertStringContainsString( '<time datetime=', $next_html );
	}

	/**
	 * Revoked rows explain that re-enrollment is required.
	 *
	 * @return void
	 */
	public function test_revoked_rows_show_reenroll_copy() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$site    = array(
			'enrollment_status'  => 'revoked',
			'polling_key_id'     => '',
			'has_polling_secret' => '0',
			'next_poll_at'       => '2026-08-13 12:30:00',
		);

		$action_html = $harness->check_form_html( $site );
		$next_html   = $harness->next_poll_line( $site );

		$this->assertStringContainsString( 'Pairing revoked locally. Re-enroll this site before manual checks are available.', $action_html );
		$this->assertStringContainsString( 'Next poll:', $next_html );
		$this->assertStringContainsString( 'Unavailable until re-enrolled', $next_html );
		$this->assertStringNotContainsString( '<time datetime=', $next_html );
	}

	/**
	 * Pending rows explain that client opt-in is still needed.
	 *
	 * @return void
	 */
	public function test_pending_rows_show_client_opt_in_copy() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$site    = array(
			'enrollment_status' => 'pending',
			'polling_key_id'    => '',
			'next_poll_at'      => '',
		);

		$this->assertStringContainsString( 'Waiting for client opt-in before manual checks are available.', $harness->check_form_html( $site ) );
		$this->assertStringContainsString( 'Waiting for client opt-in', $harness->next_poll_line( $site ) );
	}

	/**
	 * Active rows without credentials point operators toward re-enrollment.
	 *
	 * @return void
	 */
	public function test_active_missing_credentials_rows_show_missing_credentials_copy() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$site    = array(
			'enrollment_status'  => 'active',
			'polling_key_id'     => 'key-id',
			'has_polling_secret' => '0',
			'next_poll_at'       => '2026-08-13 12:30:00',
		);

		$this->assertStringContainsString( 'Polling credentials are missing. Re-enroll this site to restore manual checks.', $harness->check_form_html( $site ) );
		$this->assertStringContainsString( 'Credentials missing', $harness->next_poll_line( $site ) );
	}

	/**
	 * Paused rows show local paused next-poll copy while keeping manual checks available.
	 *
	 * @return void
	 */
	public function test_paused_rows_show_paused_polling_copy_and_manual_check_button() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$site    = array(
			'enrollment_status'  => 'active',
			'polling_key_id'     => 'key-id',
			'has_polling_secret' => '1',
			'paused_at'          => '2026-09-17 12:00:00',
			'next_poll_at'       => '',
		);

		$this->assertStringContainsString( 'Check Now', $harness->check_form_html( $site ) );
		$this->assertStringContainsString( 'Next poll:', $harness->next_poll_line( $site ) );
		$this->assertStringContainsString( 'Paused locally', $harness->next_poll_line( $site ) );
		$this->assertStringNotContainsString( '<time datetime=', $harness->next_poll_line( $site ) );
	}

	/**
	 * Active rows render a local Pause Polling control.
	 *
	 * @return void
	 */
	public function test_active_rows_render_pause_polling_form() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$site    = array(
			'enrollment_status'  => 'active',
			'polling_key_id'     => 'key-id',
			'has_polling_secret' => '1',
			'paused_at'          => '',
		);
		$html    = $harness->pause_form_html( $site );

		$this->assertStringContainsString( 'Pause Polling', $html );
		$this->assertStringContainsString( 'value="pause_polling"', $html );
		$this->assertStringContainsString( 'alynt_drime_backups_dashboard_pause_polling', $html );
	}

	/**
	 * Paused rows render a local Resume Polling control.
	 *
	 * @return void
	 */
	public function test_paused_rows_render_resume_polling_form() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$site    = array(
			'enrollment_status'  => 'active',
			'polling_key_id'     => 'key-id',
			'has_polling_secret' => '1',
			'paused_at'          => '2026-09-17 12:00:00',
		);
		$html    = $harness->pause_form_html( $site );

		$this->assertStringContainsString( 'Resume Polling', $html );
		$this->assertStringContainsString( 'value="resume_polling"', $html );
		$this->assertStringContainsString( 'alynt_drime_backups_dashboard_resume_polling', $html );
	}

	/**
	 * Revoked rows do not render polling pause controls.
	 *
	 * @return void
	 */
	public function test_revoked_rows_do_not_render_pause_polling_form() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$site    = array(
			'enrollment_status'  => 'revoked',
			'polling_key_id'     => 'key-id',
			'has_polling_secret' => '1',
		);

		$this->assertSame( '', $harness->pause_form_html( $site ) );
	}

	/**
	 * Archived rows fail closed for manual checks and scheduled-poll controls.
	 *
	 * @return void
	 */
	public function test_archived_rows_disable_manual_and_polling_controls() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$site    = array(
			'enrollment_status'  => 'active',
			'polling_key_id'     => 'key-id',
			'has_polling_secret' => '1',
			'archived_at'        => '2026-09-19 18:30:00',
		);

		$this->assertStringContainsString( 'Record archived locally. Unarchive it before manual checks are available.', $harness->check_form_html( $site ) );
		$this->assertStringContainsString( 'Archived locally', $harness->next_poll_line( $site ) );
		$this->assertSame( '', $harness->pause_form_html( $site ) );
	}

	/**
	 * Site detail polling control explains the local-only boundary.
	 *
	 * @return void
	 */
	public function test_site_detail_polling_pause_panel_explains_local_only_boundary() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$site    = array(
			'id'                 => 7,
			'enrollment_status'  => 'active',
			'polling_key_id'     => 'key-id',
			'has_polling_secret' => '1',
			'paused_at'          => '',
		);
		$html    = $harness->pause_panel_html( $site );

		$this->assertStringContainsString( 'Scheduled Polling Control', $html );
		$this->assertStringContainsString( 'changes only this dashboard record', $html );
		$this->assertStringContainsString( 'does not contact the client site', $html );
		$this->assertStringContainsString( 'Pause Polling', $html );
	}

	/**
	 * Revoked Site Detail guidance explains retention and re-enrollment without removal controls.
	 *
	 * @return void
	 */
	public function test_revoked_site_detail_guidance_explains_local_retention() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$html    = $harness->revoked_record_guidance_html(
			array(
				'id'                => 7,
				'enrollment_status' => 'revoked',
			)
		);

		$this->assertStringContainsString( 'Revoked Local Dashboard Record', $html );
		$this->assertStringContainsString( 'retained locally for audit/history', $html );
		$this->assertStringContainsString( 'create a new pairing token and complete client-site opt-in', $html );
		$this->assertStringContainsString( 'Permanent local removal is not available in this release', $html );
		$this->assertStringNotContainsString( '<form', $html );
	}

	/**
	 * Active Site Detail screens do not show revoked-record guidance.
	 *
	 * @return void
	 */
	public function test_active_site_detail_does_not_show_revoked_guidance() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$html    = $harness->revoked_record_guidance_html(
			array(
				'id'                => 7,
				'enrollment_status' => 'active',
			)
		);

		$this->assertSame( '', $html );
	}

	/**
	 * Revoked Site Detail screens expose local archive controls.
	 *
	 * @return void
	 */
	public function test_revoked_site_detail_can_show_archive_control() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$html    = $harness->archive_record_panel_html(
			array(
				'id'                => 7,
				'enrollment_status' => 'revoked',
				'archived_at'       => '',
			)
		);

		$this->assertStringContainsString( 'Local Record Visibility', $html );
		$this->assertStringContainsString( 'Archive Local Record', $html );
		$this->assertStringContainsString( 'value="archive_local"', $html );
		$this->assertStringContainsString( 'does not delete data, contact the client site, change backups, alter Drime, or reuse credentials', $html );
	}

	/**
	 * Archived Site Detail screens expose local unarchive controls.
	 *
	 * @return void
	 */
	public function test_archived_site_detail_can_show_unarchive_control() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$html    = $harness->archive_record_panel_html(
			array(
				'id'                => 7,
				'enrollment_status' => 'revoked',
				'archived_at'       => '2026-09-19 18:30:00',
			)
		);

		$this->assertStringContainsString( 'This dashboard record is archived locally', $html );
		$this->assertStringContainsString( 'Unarchive Local Record', $html );
		$this->assertStringContainsString( 'value="unarchive_local"', $html );
		$this->assertStringNotContainsString( 'restore credentials', strtolower( $html ) );
	}

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
		$this->assertStringContainsString( 'Next run 2026-09-15 18:53 UTC', $html );
		$this->assertStringContainsString( 'Alynt uploader scan cadence only; upload worker cadence may remain separate', $html );
		$this->assertStringContainsString( 'Rollback metadata captured as evidence only; rollback action unavailable', $html );
		$this->assertStringContainsString( 'Reason schedule_rollback_runtime_not_implemented', $html );
		$this->assertStringContainsString( 'metadata expires 2026-09-15 19:24 UTC', $html );
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

	/**
	 * Gets a reusable V2-capable site row for remote-action history tests.
	 *
	 * @return array<string,mixed>
	 */
	private function remote_action_history_site() {
		return array(
			'id'                            => 7,
			'enrollment_status'             => 'active',
			'polling_key_id'                => 'key-id',
			'has_polling_secret'            => '1',
			'action_key_id'                 => 'ak_test',
			'action_private_key_ciphertext' => 'ciphertext',
		);
	}

	/**
	 * Gets a reusable V2-capable snapshot for remote-action history tests.
	 *
	 * @return array<string,mixed>
	 */
	private function remote_action_history_snapshot() {
		return array(
			'decoded_payload' => array(
				'remote_actions' => array(
					'protocol_version' => 2,
					'enabled'          => true,
					'allowed_actions'  => array( 'scan_upload_now', 'schedule_preview', 'schedule_apply' ),
					'sodium_available' => true,
				),
			),
		);
	}

	/**
	 * Gets reusable remote-action rows for history filter tests.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private function remote_action_history_rows() {
		return array(
			array(
				'action_type'           => 'scan_upload_now',
				'state'                 => 'rate_limited',
				'client_state'          => 'rate_limited',
				'requested_at'          => '2026-09-15 18:00:00',
				'client_result_summary' => 'Rate limited by client.',
			),
			array(
				'action_type'           => 'schedule_preview',
				'state'                 => 'succeeded',
				'client_state'          => 'succeeded',
				'requested_at'          => '2026-09-15 18:10:00',
				'client_result_summary' => 'Schedule preview completed for Alynt scan/upload.',
			),
			array(
				'action_type'           => 'schedule_apply',
				'state'                 => 'succeeded',
				'client_state'          => 'succeeded',
				'requested_at'          => '2026-09-15 18:20:00',
				'client_result_summary' => 'Schedule apply completed for Alynt scan/upload.',
			),
		);
	}
}

/**
 * Harness exposing private polling-state rendering helpers.
 */
class Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Time_Formatters;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Local_Actions;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Archive_Actions;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Basic_Detail_Helpers;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Polling_Detail_Helpers;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Site_Detail;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Schedule_Management_Form_Helpers;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Schedule_Label_Helpers;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Request_Backup_Detail_Helpers;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Remote_Action_History_Helpers;

	/**
	 * Exposes check-status action markup.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return string
	 */
	public function check_form_html( array $site ) {
		ob_start();
		$this->render_check_status_form( $site, 7, false );
		return (string) ob_get_clean();
	}

	/**
	 * Exposes scheduled polling pause/resume markup.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return string
	 */
	public function pause_form_html( array $site ) {
		ob_start();
		$this->render_polling_pause_form( $site, 7 );
		return (string) ob_get_clean();
	}

	/**
	 * Exposes scheduled polling control panel markup.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return string
	 */
	public function pause_panel_html( array $site ) {
		ob_start();
		$this->render_polling_pause_panel( $site );
		return (string) ob_get_clean();
	}

	/**
	 * Exposes revoked-record guidance markup.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return string
	 */
	public function revoked_record_guidance_html( array $site ) {
		ob_start();
		$this->render_revoked_record_guidance( $site );
		return (string) ob_get_clean();
	}

	/**
	 * Exposes local archive-panel markup.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return string
	 */
	public function archive_record_panel_html( array $site ) {
		ob_start();
		$this->render_archive_record_panel( $site, isset( $site['id'] ) ? (int) $site['id'] : 7 );
		return (string) ob_get_clean();
	}

	/**
	 * Exposes next-poll markup.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return string
	 */
	public function next_poll_line( array $site ) {
		return $this->next_poll_html( $site );
	}

	/**
	 * Exposes V2.1 row hint markup.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @param array<string,mixed> $payload Payload.
	 * @return string
	 */
	public function request_backup_row_hint_html( array $site, array $payload ) {
		return $this->request_backup_now_row_hint( $site, $payload );
	}

	/**
	 * Exposes V2.1 detail-panel markup.
	 *
	 * @param array<string,mixed>          $site Site row.
	 * @param array<string,mixed>|null     $snapshot Snapshot row.
	 * @param array<int,array<string,mixed>> $history History rows.
	 * @return string
	 */
	public function request_backup_panel_html( array $site, $snapshot, array $history ) {
		ob_start();
		$this->render_request_backup_now_panel( $site, $snapshot, $history );
		return (string) ob_get_clean();
	}

	/**
	 * Minimal snapshot decoder needed by the included helper trait.
	 *
	 * @param array<string,mixed> $snapshot Snapshot row.
	 * @return array<string,mixed>
	 */
	private function decoded_snapshot_payload( array $snapshot ) {
		return isset( $snapshot['decoded_payload'] ) && is_array( $snapshot['decoded_payload'] ) ? $snapshot['decoded_payload'] : array();
	}

	/**
	 * Minimal backup source detail renderer needed by the included helper trait.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @return void
	 */
	private function render_backup_sources_detail( array $payload ) {
		unset( $payload );
	}
}
