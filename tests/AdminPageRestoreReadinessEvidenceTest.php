<?php
/**
 * Admin restore-readiness evidence rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-restore-readiness-test-harness.php';

/**
 * Tests restore-readiness evidence rendering helpers.
 */
class AdminPageRestoreReadinessEvidenceTest extends TestCase {
	/**
	 * Site Detail renders read-only restore-readiness evidence and no restore control.
	 *
	 * @return void
	 */
	public function test_restore_readiness_panel_is_evidence_only() {
		$harness = new Alynt_Drime_Backups_Dashboard_Restore_Readiness_Test_Harness();
		$html    = $harness->panel_html(
			array(
				'payload_json' => wp_json_encode(
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
				),
			)
		);

		$this->assertStringContainsString( 'Restore Readiness Evidence', $html );
		$this->assertStringContainsString( 'The dashboard cannot restore this site', $html );
		$this->assertStringContainsString( 'Server runner', $html );
		$this->assertStringContainsString( 'WPvivid', $html );
		$this->assertStringContainsString( 'Evidence available', $html );
		$this->assertStringContainsString( 'Not verified', $html );
		$this->assertStringContainsString( 'Opaque client reference reported', $html );
		$this->assertStringContainsString( 'restore_evidence_incomplete', $html );
		$this->assertStringNotContainsString( 'opaque-client-ref', $html );
		$this->assertStringNotContainsString( '<button', $html );
		$this->assertStringNotContainsString( 'Restore Now', $html );
	}

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

	/**
	 * Missing restore-readiness evidence renders no panel.
	 *
	 * @return void
	 */
	public function test_missing_restore_readiness_renders_no_panel() {
		$harness = new Alynt_Drime_Backups_Dashboard_Restore_Readiness_Test_Harness();
		$html    = $harness->panel_html(
			array(
				'payload_json' => wp_json_encode(
					array(
						'schema_version' => 1,
					)
				),
			)
		);

		$this->assertSame( '', $html );
		$this->assertSame( '', $harness->row_hint_html( array( 'schema_version' => 1 ) ) );
	}
}
