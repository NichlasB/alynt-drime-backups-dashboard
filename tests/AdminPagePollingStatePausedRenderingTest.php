<?php
/**
 * Admin polling-state paused rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-polling-state-rendering-bootstrap.php';

/**
 * Tests paused Sites-row manual check and next-poll rendering states.
 */
class AdminPagePollingStatePausedRenderingTest extends TestCase {
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
}
