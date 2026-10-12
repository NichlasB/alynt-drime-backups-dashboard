<?php
/**
 * Admin diagnostics rollback rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-diagnostics-rendering-test-harness.php';

/**
 * Tests diagnostics rollback action-history rendering.
 */
class AdminPageDiagnosticsRollbackRenderingTest extends TestCase {
	/**
	 * Rollback-preview audit rows render as operator-friendly labels.
	 *
	 * @return void
	 */
	public function test_audit_history_renders_rollback_preview_action_as_operator_label() {
		$harness = new Alynt_Drime_Backups_Dashboard_Diagnostics_Rendering_Test_Harness();
		$html    = $harness->audit_history_html(
			array(
				'summary' => array(
					'total'          => 1,
					'last_action_at' => '2026-09-21 16:00:00',
				),
				'events'  => array(
					array(
						'timestamp' => '2026-09-21 16:00:00',
						'actor_id'  => 12,
						'action'    => 'preview_schedule_rollback',
						'outcome'   => 'succeeded',
						'context'   => array(
							'dashboard_site_id' => 7,
							'action_type'       => 'schedule_rollback_preview',
						),
					),
				),
			)
		);

		$this->assertStringContainsString( 'Preview Schedule Rollback', $html );
		$this->assertStringContainsString( 'schedule_rollback_preview', $html );
		$this->assertStringNotContainsString( '>preview_schedule_rollback<', $html );
	}
}
