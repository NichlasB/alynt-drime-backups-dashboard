<?php
/**
 * Diagnostics support action-summary tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/diagnostics-test-bootstrap.php';

/**
 * Tests support-safe action aggregate summaries.
 */
class DiagnosticsSupportActionSummaryTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Test_Fixtures;

	/**
	 * Support action summaries include preview-only action counts as aggregates only.
	 *
	 * @return void
	 */
	public function test_support_action_summary_includes_preview_only_action_counts() {
		$harness = new Alynt_Drime_Backups_Dashboard_Diagnostics_Support_Test_Harness();
		$actions = $harness->support_action_summary(
			array(
				'total'                     => 7,
				'client_reconciled'         => 5,
				'stale'                     => 1,
				'awaiting_confirmation'     => 2,
				'schedule_apply'            => 3,
				'schedule_rollback_preview' => 2,
				'cleanup_preview'           => 1,
				'rollback_metadata'         => 1,
				'latest_updated_at'         => '2026-09-21 16:00:00',
			)
		);

		$this->assertSame( 7, $actions['total'] );
		$this->assertSame( 3, $actions['schedule_apply'] );
		$this->assertSame( 2, $actions['schedule_rollback_preview'] );
		$this->assertSame( 1, $actions['cleanup_preview'] );
		$this->assertSame( 1, $actions['rollback_metadata'] );
		$this->assertArrayNotHasKey( 'redacted_context_json', $actions );
		$this->assertArrayNotHasKey( 'source_apply_action_id', $actions );
	}

	/**
	 * Support action summaries keep preview-only action counts non-negative.
	 *
	 * @return void
	 */
	public function test_support_action_summary_bounds_preview_only_action_counts() {
		$harness = new Alynt_Drime_Backups_Dashboard_Diagnostics_Support_Test_Harness();
		$actions = $harness->support_action_summary(
			array(
				'schedule_rollback_preview' => -4,
				'cleanup_preview'           => -2,
			)
		);

		$this->assertSame( 0, $actions['schedule_rollback_preview'] );
		$this->assertSame( 0, $actions['cleanup_preview'] );
	}

}