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
