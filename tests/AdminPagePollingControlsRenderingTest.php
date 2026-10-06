<?php
/**
 * Admin polling-control rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-polling-state-rendering-bootstrap.php';

/**
 * Tests dashboard-local scheduled polling controls.
 */
class AdminPagePollingControlsRenderingTest extends TestCase {
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
