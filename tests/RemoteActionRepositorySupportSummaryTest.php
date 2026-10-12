<?php
/**
 * Remote action repository support-summary tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/remote-action-repository-test-bootstrap.php';
require_once __DIR__ . '/support/remote-action-repository-client-report-rollback-summary-fixtures.php';

/**
 * Tests support-safe remote-action repository aggregates.
 */
class RemoteActionRepositorySupportSummaryTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Client_Report_Rollback_Summary_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Test_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_WPDB_Setup;

	/**
	 * Support summaries count rollback-readiness evidence without exposing details.
	 *
	 * @return void
	 */
	public function test_support_summary_counts_schedule_apply_rollback_metadata() {
		$repository      = $this->remote_action_repository();
		$this->wpdb->row = $this->rollback_metadata_support_summary_row();

		$summary = $repository->support_summary();

		$this->assertSame( 4, $summary['total'] );
		$this->assertSame( 2, $summary['schedule_apply'] );
		$this->assertSame( 1, $summary['schedule_rollback_preview'] );
		$this->assertSame( 1, $summary['rollback_metadata'] );
		$this->assertStringContainsString( "action_type = 'schedule_apply'", $this->wpdb->last_query );
		$this->assertStringContainsString( "action_type = 'schedule_rollback_preview'", $this->wpdb->last_query );
		$this->assertStringContainsString( 'rollback_metadata', $this->wpdb->last_query );
		$this->assertArrayNotHasKey( 'redacted_context_json', $summary );
	}
}
