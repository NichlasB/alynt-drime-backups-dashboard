<?php
/**
 * Admin site-detail attention history bounds tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-polling-state-rendering-bootstrap.php';

/**
 * Tests Site Detail attention and recovery history bounds.
 */
class AdminPageSiteDetailAttentionHistoryBoundsTest extends TestCase {
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
