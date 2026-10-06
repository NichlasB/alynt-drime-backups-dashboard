<?php
/**
 * Remote action repository client-report tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/remote-action-repository-test-bootstrap.php';

/**
 * Tests support-safe client report context preservation.
 */
class RemoteActionRepositoryClientReportTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Test_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_WPDB_Setup;

	/**
	 * Client schedule apply reports preserve support-safe apply details.
	 *
	 * @return void
	 */
	public function test_mark_client_report_preserves_schedule_apply_details() {
		$context = $this->client_report_context(
			array(
				'state'          => 'succeeded',
				'result_code'    => 'schedule_apply_succeeded',
				'result_summary' => 'Schedule apply completed for Alynt scan/upload.',
				'schedule_apply' => array(
					'schedule_id'          => 'alynt_scan_upload',
					'label'                => 'Alynt scan/upload',
					'owner'                => 'alynt_uploader',
					'capability_version'   => 1,
					'preview_action_id'    => '22222222-2222-4222-8222-222222222222',
					'preview_fingerprint'  => str_repeat( 'a', 64 ),
					'proposed_cadence'     => 'every_30_minutes',
					'previous_cadence'     => 'every_15_minutes',
					'applied_cadence'      => 'every_30_minutes',
					'previous_next_run_at' => '2026-09-15T18:30:03+00:00',
					'applied_next_run_at'  => '2026-09-15T18:53:55+00:00',
					'changed'              => true,
					'rollback_available'   => true,
					'rollback_metadata'    => array(
						'captured'                            => true,
						'available'                           => true,
						'reason'                              => 'schedule_rollback_runtime_not_implemented',
						'source_action_id'                    => '33333333-3333-4333-8333-333333333333',
						'source_preview_action_id'            => '22222222-2222-4222-8222-222222222222',
						'schedule_id'                         => 'alynt_scan_upload',
						'owner'                               => 'alynt_uploader',
						'previous_cadence'                    => 'every_15_minutes',
						'applied_cadence'                     => 'every_30_minutes',
						'previous_next_run_at'                => '2026-09-15T18:30:03+00:00',
						'applied_next_run_at'                 => '2026-09-15T18:53:55+00:00',
						'current_schedule_fingerprint_before' => str_repeat( 'b', 64 ),
						'current_schedule_fingerprint_after'  => str_repeat( 'c', 64 ),
						'rollback_metadata_fingerprint'       => str_repeat( 'd', 64 ),
						'captured_at'                         => '2026-09-15T18:24:12+00:00',
						'expires_at'                          => '2026-09-15T19:24:12+00:00',
					),
				),
			),
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
			array(
				'state'                     => 'succeeded',
				'result_code'               => 'schedule_rollback_preview_ready',
				'result_summary'            => 'Schedule rollback preview completed.',
				'schedule_rollback_preview' => array(
					'schedule_id'                           => 'alynt_scan_upload',
					'label'                                 => 'Alynt scan/upload',
					'owner'                                 => 'alynt_uploader',
					'current_cadence'                       => 'every_30_minutes',
					'applied_cadence'                       => 'every_30_minutes',
					'rollback_cadence'                      => 'every_15_minutes',
					'current_next_run_at'                   => '2026-09-15T18:53:55+00:00',
					'rollback_next_run_estimate_at'         => '2026-09-15T18:45:00+00:00',
					'would_change'                          => true,
					'rollback_apply_supported'              => true,
					'rollback_supported'                    => true,
					'preview_action_id'                     => '44444444-4444-4444-8444-444444444444',
					'preview_fingerprint'                   => str_repeat( 'a', 64 ),
					'source_apply_action_id'                => '33333333-3333-4333-8333-333333333333',
					'rollback_metadata_fingerprint'         => str_repeat( 'b', 64 ),
					'current_schedule_fingerprint'          => str_repeat( 'c', 64 ),
					'expected_current_schedule_fingerprint' => str_repeat( 'd', 64 ),
					'previous_schedule_fingerprint'         => str_repeat( 'e', 64 ),
					'capability_version'                    => 1,
					'preview_created_at'                    => '2026-09-15T18:35:00+00:00',
					'preview_expires_at'                    => '2026-09-15T18:50:00+00:00',
				),
			),
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
		$this->wpdb->row = array(
			'total'                      => 4,
			'client_reconciled'          => 3,
			'stale'                      => 1,
			'awaiting_confirmation'      => 1,
			'schedule_apply'             => 2,
			'schedule_rollback_preview'  => 1,
			'rollback_metadata_captured' => 1,
			'latest_updated_at'          => '2026-09-15 19:24:12',
		);

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
