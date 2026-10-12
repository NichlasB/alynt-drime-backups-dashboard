<?php
/**
 * Admin polling-state rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-polling-state-rendering-bootstrap.php';

/**
 * Tests credential-aware Sites-row manual check and next-poll rendering.
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
