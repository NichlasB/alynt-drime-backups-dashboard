<?php
/**
 * Admin polling-state rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-polling-state-rendering-bootstrap.php';

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
		$site    = $this->polling_site();

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
		$site    = $this->polling_site(
			array(
				'enrollment_status'  => 'revoked',
				'polling_key_id'     => '',
				'has_polling_secret' => '0',
			)
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
		$site    = $this->polling_site(
			array(
				'enrollment_status' => 'pending',
				'polling_key_id'    => '',
				'next_poll_at'      => '',
			)
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
		$site    = $this->polling_site(
			array(
				'has_polling_secret' => '0',
			)
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
		$site    = $this->polling_site(
			array(
				'paused_at'    => '2026-09-17 12:00:00',
				'next_poll_at' => '',
			)
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
		$site    = $this->polling_site();
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
		$site    = $this->polling_site(
			array(
				'paused_at' => '2026-09-17 12:00:00',
			)
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
		$site    = $this->polling_site(
			array(
				'enrollment_status' => 'revoked',
			)
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
		$site    = $this->polling_site(
			array(
				'archived_at' => '2026-09-19 18:30:00',
			)
		);

		$this->assertStringContainsString( 'Record archived locally. Unarchive it before manual checks are available.', $harness->check_form_html( $site ) );
		$this->assertStringContainsString( 'Archived locally', $harness->next_poll_line( $site ) );
		$this->assertSame( '', $harness->pause_form_html( $site ) );
	}

	/**
	 * Returns a default active polling site row with optional overrides.
	 *
	 * @param array<string,mixed> $overrides Site row overrides.
	 * @return array<string,mixed>
	 */
	private function polling_site( array $overrides = array() ) {
		return array_merge(
			array(
				'enrollment_status'  => 'active',
				'polling_key_id'     => 'key-id',
				'has_polling_secret' => '1',
				'paused_at'          => '',
				'next_poll_at'       => '2026-08-13 12:30:00',
			),
			$overrides
		);
	}

}
