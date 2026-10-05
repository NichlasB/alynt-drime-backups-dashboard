<?php
/**
 * Diagnostics tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/diagnostics-test-bootstrap.php';

/**
 * Tests dashboard diagnostics counts and support-safe record/source aggregates.
 */
class DiagnosticsTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Test_Fixtures;

	/**
	 * Diagnostics count polling-ready, due, paused, and failed sites.
	 *
	 * @return void
	 */
	public function test_collect_counts_polling_states() {
		$diagnostics = new Alynt_Drime_Backups_Dashboard_Diagnostics(
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Site_Repository(
				array(
					$this->site(
						1,
						array(
							'overall_status'          => 'working',
							'next_poll_at'            => '2020-01-01 00:00:00',
							'last_poll_attempt_at'    => '2026-08-10 08:00:00',
							'last_seen_at'            => '2026-08-10 08:00:00',
							'consecutive_failures'    => 1,
							'last_error_code'         => 'transport_failed',
							'last_error_summary'      => 'Client status endpoint unavailable.',
							'polling_key_id'          => 'pk_example_0000000000000000',
							'polling_secret_ciphertext' => 'adbv1.ciphertext',
						)
					),
					$this->site(
						2,
						array(
							'enrollment_status' => 'awaiting_first_poll',
							'overall_status'    => 'pending',
							'polling_key_id'    => '',
						)
					),
					$this->site(
						3,
						array(
							'overall_status' => 'working',
							'paused_at'      => '2026-08-10 08:05:00',
						)
					),
				)
			),
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Snapshot_Repository(
				array(
					1 => $this->snapshot(),
					3 => $this->snapshot(),
				)
			),
			new Alynt_Drime_Backups_Dashboard_Status_Classifier()
		);

		$result = $diagnostics->collect();

		$this->assertSame( 3, $result['counts']['total_sites'] );
		$this->assertSame( 1, $result['counts']['polling_ready'] );
		$this->assertSame( 2, $result['counts']['not_polling'] );
		$this->assertSame( 1, $result['counts']['due_now'] );
		$this->assertSame( 1, $result['counts']['missing_credentials'] );
		$this->assertSame( 1, $result['counts']['paused'] );
		$this->assertSame( 1, $result['counts']['with_failures'] );
		$this->assertSame( 2, $result['counts']['record_states']['active'] );
		$this->assertSame( 1, $result['counts']['record_states']['awaiting_first_poll'] );
		$this->assertSame( 0, $result['counts']['record_states']['pending'] );
		$this->assertSame( 0, $result['counts']['record_states']['revoked'] );
		$this->assertSame( 0, $result['counts']['backup_sources']['reporting_sites'] );
		$this->assertSame( 'unavailable', $result['scheduler']['poll_schedule_state'] );
		$this->assertSame( 30, $result['scheduler']['retention_days'] );
	}

	/**
	 * Diagnostics expose support-safe aggregate record states.
	 *
	 * @return void
	 */
	public function test_record_state_diagnostics_explain_non_polling_records() {
		$diagnostics = new Alynt_Drime_Backups_Dashboard_Diagnostics(
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Site_Repository(
				array(
					$this->site(
						1,
						array(
							'enrollment_status' => 'pending',
							'overall_status'    => 'pending',
							'polling_key_id'    => '',
						)
					),
					$this->site(
						2,
						array(
							'enrollment_status' => 'revoked',
							'overall_status'    => 'pending',
							'polling_key_id'    => '',
						)
					),
					$this->site(
						3,
						array(
							'enrollment_status' => '',
							'overall_status'    => 'pending',
							'polling_key_id'    => '',
						)
					),
					$this->site(
						4,
						array(
							'enrollment_status' => 'revoked',
							'overall_status'    => 'pending',
							'polling_key_id'    => '',
							'archived_at'       => '2026-09-19 18:30:00',
						)
					),
				)
			),
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Snapshot_Repository( array() ),
			new Alynt_Drime_Backups_Dashboard_Status_Classifier()
		);

		$result  = $diagnostics->collect();
		$encoded = wp_json_encode( $result['support'] );

		$this->assertSame( 4, $result['counts']['total_sites'] );
		$this->assertSame( 0, $result['counts']['polling_ready'] );
		$this->assertSame( 4, $result['counts']['not_polling'] );
		$this->assertSame( 1, $result['counts']['record_states']['pending'] );
		$this->assertSame( 1, $result['counts']['record_states']['revoked'] );
		$this->assertSame( 1, $result['counts']['record_states']['archived'] );
		$this->assertSame( 1, $result['counts']['record_states']['unknown'] );
		$this->assertStringContainsString( 'record_states', $encoded );
		$this->assertStringContainsString( 'not_polling', $encoded );
		$this->assertStringNotContainsString( 'Client 1', $encoded );
		$this->assertStringNotContainsString( 'client1.example.com', $encoded );
	}

	/**
	 * Backup source diagnostics are aggregate-only.
	 *
	 * @return void
	 */
	public function test_backup_source_diagnostics_are_aggregate_only() {
		$diagnostics = new Alynt_Drime_Backups_Dashboard_Diagnostics(
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Site_Repository(
				array(
					$this->site( 1 ),
				)
			),
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Snapshot_Repository(
				array(
					1 => $this->snapshot(
						array(
							'backup_sources' => array(
								'server'  => array(
									'freshness_status' => 'stale',
								),
								'wpvivid' => array(
									'freshness_status' => 'no_upload_evidence',
								),
							),
						)
					),
				)
			),
			new Alynt_Drime_Backups_Dashboard_Status_Classifier()
		);

		$result  = $diagnostics->collect();
		$encoded = wp_json_encode( $result['support'] );

		$this->assertSame( 1, $result['counts']['backup_sources']['reporting_sites'] );
		$this->assertSame( 1, $result['counts']['backup_sources']['stale_sources'] );
		$this->assertSame( 1, $result['counts']['backup_sources']['no_upload_evidence_sources'] );
		$this->assertStringContainsString( 'backup_sources', $encoded );
		$this->assertStringNotContainsString( 'client1.example.com', $encoded );
		$this->assertStringNotContainsString( 'Client 1', $encoded );
	}

}