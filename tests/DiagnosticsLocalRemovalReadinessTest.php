<?php
/**
 * Diagnostics local-removal readiness tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/diagnostics-test-bootstrap.php';

/**
 * Tests support-safe diagnostics counts for retained local-record removal readiness.
 */
class DiagnosticsLocalRemovalReadinessTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Test_Fixtures;

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
						'enrollment_status'             => 'active',
						'overall_status'                => 'working',
						'action_key_id'                 => 'adba_test_key',
						'action_private_key_ciphertext' => 'adbv2.action.ciphertext',
						'archived_at'                   => '2026-09-19 18:32:00',
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
