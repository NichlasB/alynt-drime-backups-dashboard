<?php
/**
 * Admin polling-state unavailable rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-polling-state-rendering-bootstrap.php';

/**
 * Tests unavailable Sites-row manual check and next-poll rendering states.
 */
class AdminPagePollingStateUnavailableRenderingTest extends TestCase {
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
