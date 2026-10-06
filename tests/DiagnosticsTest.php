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
		$result = $this->collect_diagnostics(
			array(
				$this->site(
					1,
					array(
						'overall_status'            => 'working',
						'next_poll_at'              => '2020-01-01 00:00:00',
						'last_poll_attempt_at'      => '2026-08-10 08:00:00',
						'last_seen_at'              => '2026-08-10 08:00:00',
						'consecutive_failures'      => 1,
						'last_error_code'           => 'transport_failed',
						'last_error_summary'        => 'Client status endpoint unavailable.',
						'polling_key_id'            => 'pk_example_0000000000000000',
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
			),
			array(
				1 => $this->snapshot(),
				3 => $this->snapshot(),
			)
		);

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
		$result = $this->collect_diagnostics(
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
		);

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
	 * Diagnostics expose aggregate local removal-readiness evidence for archived records.
	 *
	 * @return void
	 */
	public function test_local_removal_readiness_counts_archived_records() {
		$result = $this->collect_diagnostics(
			array(
				$this->site(
					1,
					array(
						'enrollment_status'         => 'revoked',
						'overall_status'            => 'pending',
						'polling_key_id'            => '',
						'polling_secret_ciphertext' => '',
						'next_poll_at'              => '',
						'archived_at'               => '2026-09-19 18:30:00',
					)
				),
				$this->site(
					2,
					array(
						'enrollment_status'         => 'revoked',
						'overall_status'            => 'pending',
						'polling_key_id'            => '',
						'polling_secret_ciphertext' => '',
						'next_poll_at'              => '',
						'archived_at'               => '2026-09-19 18:31:00',
					)
				),
				$this->site(
					3,
					array(
						'enrollment_status'          => 'active',
						'overall_status'             => 'working',
						'action_key_id'              => 'adba_test_key',
						'action_private_key_ciphertext' => 'adbv2.action.ciphertext',
						'archived_at'                => '2026-09-19 18:32:00',
					)
				),
			),
			array(),
			array(
				1 => array(
					$this->snapshot_row( 'working', '2026-09-18 12:00:00' ),
					$this->snapshot_row( 'pending', '2026-09-18 11:45:00' ),
				),
				2 => array(
					$this->snapshot_row( 'working', '2026-09-18 12:10:00' ),
				),
			),
			array(
				1 => 3,
				2 => 2,
				3 => 4,
			),
			array(
				2 => 1,
			)
		);

		$local_removal = $result['counts']['local_removal'];
		$encoded       = wp_json_encode( $result['support'] );

		$this->assertSame( 3, $local_removal['archived_records'] );
		$this->assertSame( 1, $local_removal['ready_records'] );
		$this->assertSame( 2, $local_removal['blocked_records'] );
		$this->assertSame( 3, $local_removal['retained_snapshot_rows'] );
		$this->assertSame( 9, $local_removal['retained_action_rows'] );
		$this->assertSame( 1, $local_removal['non_terminal_action_rows'] );
		$this->assertStringContainsString( 'local_removal', $encoded );
		$this->assertStringNotContainsString( 'Client 1', $encoded );
		$this->assertStringNotContainsString( 'client1.example.com', $encoded );
	}

}
