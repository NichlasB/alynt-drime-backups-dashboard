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
				'action_type'    => 'scan_upload_now',
				'state'          => 'queued_for_dispatch',
				'requested_at'   => '2026-08-20 12:00:00',
				'result_summary' => 'Stored locally only.',
			),
		);
		$html     = $harness->request_backup_panel_html( $site, $snapshot, $history );

		$this->assertStringContainsString( 'Request Backup Now', $html );
		$this->assertStringContainsString( 'Capability reported', $html );
		$this->assertStringContainsString( '<form method="post"', $html );
		$this->assertStringContainsString( 'value="request_backup_now"', $html );
		$this->assertStringContainsString( 'value="7"', $html );
		$this->assertStringContainsString( 'Queued for dispatch', $html );
		$this->assertStringContainsString( 'Stored locally only.', $html );
		$this->assertStringNotContainsString( 'private', strtolower( $html ) );
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

/**
 * Harness exposing private polling-state rendering helpers.
 */
class Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Time_Formatters;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Basic_Detail_Helpers;

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
