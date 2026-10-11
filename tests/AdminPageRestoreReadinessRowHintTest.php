<?php
/**
 * Admin restore-readiness row-hint rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-restore-readiness-test-harness.php';

/**
 * Tests compact Sites-row restore-readiness evidence hints.
 */
class AdminPageRestoreReadinessRowHintTest extends TestCase {
	/**
	 * Sites-row restore-readiness hint stays compact and evidence-only.
	 *
	 * @return void
	 */
	public function test_restore_readiness_row_hint_summarizes_candidate_evidence() {
		$harness = new Alynt_Drime_Backups_Dashboard_Restore_Readiness_Test_Harness();
		$html    = $harness->row_hint_html(
			array(
				'restore_readiness' => array(
					'generated_at'  => '2026-10-02T12:00:00Z',
					'overall_state' => 'evidence_available',
					'candidates'    => array(
						array(
							'source'                    => 'server',
							'candidate_ref'             => 'opaque-client-ref',
							'latest_backup_finished_at' => '2026-10-02T01:30:00Z',
							'component_state'           => 'complete',
							'checksum_state'            => 'verified',
							'manifest_state'            => 'compatible',
							'sidecar_state'             => 'present',
							'warnings'                  => array(),
						),
						array(
							'source'          => 'wpvivid',
							'component_state' => 'unknown',
							'checksum_state'  => 'not_reported',
							'manifest_state'  => 'not_reported',
							'sidecar_state'   => 'not_reported',
							'warnings'        => array( 'restore_evidence_incomplete' ),
						),
					),
				),
			)
		);

		$this->assertStringContainsString( 'Restore evidence:', $html );
		$this->assertStringContainsString( 'Evidence available', $html );
		$this->assertStringContainsString( 'Server runner', $html );
		$this->assertStringContainsString( 'WPvivid', $html );
		$this->assertStringContainsString( 'Checksum: Verified', $html );
		$this->assertStringContainsString( 'Manifest: Compatible', $html );
		$this->assertStringContainsString( 'Not verified', $html );
		$this->assertStringNotContainsString( 'opaque-client-ref', $html );
		$this->assertStringNotContainsString( 'restore_evidence_incomplete', $html );
		$this->assertStringNotContainsString( '<button', $html );
		$this->assertStringNotContainsString( 'Restore Now', $html );
	}
}
