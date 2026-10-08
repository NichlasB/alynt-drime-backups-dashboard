<?php
/**
 * Remote action repository client-report tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/remote-action-repository-test-bootstrap.php';
require_once __DIR__ . '/support/remote-action-repository-client-report-fixtures.php';

/**
 * Tests support-safe client report context preservation.
 */
class RemoteActionRepositoryClientReportTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Client_Report_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Test_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_WPDB_Setup;

	/**
	 * Client schedule apply reports preserve support-safe apply details.
	 *
	 * @return void
	 */
	public function test_mark_client_report_preserves_schedule_apply_details() {
		$context = $this->client_report_context(
			$this->schedule_apply_client_report(),
			'2026-09-15 18:24:12'
		);

		$this->assertSame( 'alynt_scan_upload', $context['schedule_apply']['schedule_id'] );
		$this->assertSame( 'every_15_minutes', $context['schedule_apply']['previous_cadence'] );
		$this->assertSame( 'every_30_minutes', $context['schedule_apply']['applied_cadence'] );
		$this->assertSame( '2026-09-15T18:53:55+00:00', $context['schedule_apply']['new_next_run_at'] );
		$this->assertSame( '22222222-2222-4222-8222-222222222222', $context['schedule_apply']['preview_action_id'] );
		$this->assertTrue( $context['schedule_apply']['changed'] );
		$this->assertFalse( $context['schedule_apply']['rollback_available'] );
		$this->assertTrue( $context['schedule_apply']['rollback_metadata']['captured'] );
		$this->assertFalse( $context['schedule_apply']['rollback_metadata']['available'] );
		$this->assertSame( 'schedule_rollback_runtime_not_implemented', $context['schedule_apply']['rollback_metadata']['reason'] );
		$this->assertSame( str_repeat( 'b', 64 ), $context['schedule_apply']['rollback_metadata']['current_schedule_fingerprint_before'] );
		$this->assertSame( str_repeat( 'c', 64 ), $context['schedule_apply']['rollback_metadata']['current_schedule_fingerprint_after'] );
		$this->assertSame( str_repeat( 'd', 64 ), $context['schedule_apply']['rollback_metadata']['rollback_metadata_fingerprint'] );
	}

	/**
	 * Client rollback-preview reports preserve support-safe preview details.
	 *
	 * @return void
	 */
	public function test_mark_client_report_preserves_schedule_rollback_preview_details() {
		$context = $this->client_report_context(
			$this->schedule_rollback_preview_client_report(),
			'2026-09-15 18:35:12'
		);

		$this->assertSame( 'alynt_scan_upload', $context['schedule_rollback_preview']['schedule_id'] );
		$this->assertSame( 'every_30_minutes', $context['schedule_rollback_preview']['current_cadence'] );
		$this->assertSame( 'every_15_minutes', $context['schedule_rollback_preview']['rollback_cadence'] );
		$this->assertTrue( $context['schedule_rollback_preview']['would_change'] );
		$this->assertFalse( $context['schedule_rollback_preview']['rollback_apply_supported'] );
		$this->assertFalse( $context['schedule_rollback_preview']['rollback_supported'] );
		$this->assertSame( '33333333-3333-4333-8333-333333333333', $context['schedule_rollback_preview']['source_apply_action_id'] );
		$this->assertSame( str_repeat( 'b', 64 ), $context['schedule_rollback_preview']['rollback_metadata_fingerprint'] );
	}

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

	/**
	 * Marks a client report and returns the stored redacted context.
	 *
	 * @param array<string,mixed> $report Client report.
	 * @param string              $reported_at Reported time.
	 * @return array<string,mixed>
	 */
	private function client_report_context( array $report, $reported_at ) {
		$repository      = $this->remote_action_repository();
		$this->wpdb->row = $this->empty_remote_action_row();

		$this->assertTrue( $repository->mark_client_report( 321, $report, $reported_at ) );

		return json_decode( $this->wpdb->updated_data['redacted_context_json'], true );
	}

}
