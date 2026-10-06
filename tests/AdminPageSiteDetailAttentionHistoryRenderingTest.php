<?php
/**
 * Admin site-detail attention history rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-polling-state-rendering-bootstrap.php';

/**
 * Tests Site Detail attention and recovery history rendering.
 */
class AdminPageSiteDetailAttentionHistoryRenderingTest extends TestCase {
	/**
	 * Site Detail recovery history shows transient attention and recovery changes.
	 *
	 * @return void
	 */
	public function test_attention_recovery_history_summarizes_transitions() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$history = array(
			array(
				'observed_at'    => '2026-09-29 03:00:00',
				'overall_status' => 'working',
				'queue_count'    => 0,
				'uploaded_count' => 48,
				'failed_count'   => 0,
				'warning_count'  => 0,
				'cron_status'    => 'likely_configured',
			),
			array(
				'observed_at'    => '2026-09-28 03:00:00',
				'overall_status' => 'needs_attention',
				'queue_count'    => 0,
				'uploaded_count' => 47,
				'failed_count'   => 0,
				'warning_count'  => 1,
				'cron_status'    => 'likely_configured',
			),
			array(
				'observed_at'    => '2026-09-27 03:00:00',
				'overall_status' => 'working',
				'queue_count'    => 0,
				'uploaded_count' => 47,
				'failed_count'   => 0,
				'warning_count'  => 0,
				'cron_status'    => 'likely_configured',
			),
		);

		$html = $harness->attention_recovery_history_html( $history );

		$this->assertStringContainsString( 'Attention / Recovery History', $html );
		$this->assertStringContainsString( 'Recovered from Needs attention to Working.', $html );
		$this->assertStringContainsString( 'Entered Needs attention from Working.', $html );
		$this->assertStringContainsString( 'Queue 0; Uploaded 48; Failed 0; Warnings 0; Cron likely_configured', $html );
	}

	/**
	 * Site Detail recovery history stays quiet when retained snapshots did not change status.
	 *
	 * @return void
	 */
	public function test_attention_recovery_history_empty_state_for_unchanged_snapshots() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$history = array(
			array(
				'observed_at'    => '2026-09-29 03:00:00',
				'overall_status' => 'working',
			),
			array(
				'observed_at'    => '2026-09-28 03:00:00',
				'overall_status' => 'working',
			),
		);

		$html = $harness->attention_recovery_history_html( $history );

		$this->assertStringContainsString( 'No recent attention or recovery transitions are available in the retained snapshot window.', $html );
		$this->assertStringNotContainsString( 'Recovered from', $html );
		$this->assertStringNotContainsString( 'Entered Needs attention', $html );
	}

	/**
	 * Site Detail recovery history keeps the latest bounded transitions.
	 *
	 * @return void
	 */
	public function test_attention_recovery_history_keeps_latest_bounded_transitions() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$history = array(
			array(
				'observed_at'    => '2026-09-30 03:00:00',
				'overall_status' => 'working',
			),
			array(
				'observed_at'    => '2026-09-29 03:00:00',
				'overall_status' => 'needs_attention',
			),
			array(
				'observed_at'    => '2026-09-28 03:00:00',
				'overall_status' => 'working',
			),
			array(
				'observed_at'    => '2026-09-27 03:00:00',
				'overall_status' => 'needs_attention',
			),
			array(
				'observed_at'    => '2026-09-26 03:00:00',
				'overall_status' => 'working',
			),
			array(
				'observed_at'    => '2026-09-25 03:00:00',
				'overall_status' => 'needs_attention',
			),
			array(
				'observed_at'    => '2026-09-24 03:00:00',
				'overall_status' => 'working',
			),
		);

		$html = $harness->attention_recovery_history_html( $history );

		$this->assertStringContainsString( '2026-09-30T03:00:00+00:00', $html );
		$this->assertStringContainsString( '2026-09-26T03:00:00+00:00', $html );
		$this->assertStringNotContainsString( '2026-09-25T03:00:00+00:00', $html );
	}
}
