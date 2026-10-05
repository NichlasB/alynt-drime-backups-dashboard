<?php
/**
 * Diagnostics remote-action aggregate tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/diagnostics-test-bootstrap.php';

/**
 * Tests support-safe remote-action aggregate diagnostics.
 */
class DiagnosticsRemoteActionAggregatesTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Test_Fixtures;

	/**
	 * Schedule-management diagnostics are aggregate-only.
	 *
	 * @return void
	 */
	public function test_schedule_management_diagnostics_are_aggregate_only() {
		$diagnostics = new Alynt_Drime_Backups_Dashboard_Diagnostics(
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Site_Repository(
				array(
					$this->site( 1 ),
					$this->site( 2 ),
					$this->site( 3 ),
				)
			),
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Snapshot_Repository(
				array(
					1 => $this->snapshot(
						array(
							'remote_actions' => array(
								'protocol_version'    => 2,
								'enabled'             => true,
								'schedule_management' => array(
									'protocol_version'   => 2,
									'capability_version' => 1,
									'enabled'            => true,
									'preview_only'       => true,
									'apply_supported'    => false,
									'rollback_supported' => false,
									'schedules'          => array(
										array(
											'schedule_id'       => 'alynt_scan_upload',
											'current_cadence'   => 'every_15_minutes',
										),
									),
								),
							),
						)
					),
					2 => $this->snapshot(),
					3 => $this->snapshot(
						array(
							'remote_actions' => array(
								'protocol_version'    => 2,
								'enabled'             => true,
								'schedule_management' => array(
									'protocol_version'            => 2,
									'capability_version'          => 1,
									'enabled'                     => true,
									'preview_only'                => false,
									'apply_supported'             => true,
									'rollback_preview_supported'  => true,
									'rollback_supported'          => false,
									'schedules'                   => array(
										array(
											'schedule_id'                => 'alynt_scan_upload',
											'current_cadence'            => 'every_30_minutes',
											'rollback_preview_supported' => true,
										),
									),
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

		$this->assertSame( 2, $result['counts']['schedule_management']['reporting_sites'] );
		$this->assertSame( 1, $result['counts']['schedule_management']['preview_only_sites'] );
		$this->assertSame( 1, $result['counts']['schedule_management']['apply_sites'] );
		$this->assertSame( 1, $result['counts']['schedule_management']['unavailable_sites'] );
		$this->assertSame( 2, $result['counts']['schedule_management']['reported_schedules'] );
		$this->assertSame( 1, $result['counts']['schedule_management']['rollback_preview_supported_sites'] );
		$this->assertSame( 1, $result['counts']['schedule_management']['rollback_preview_hidden_sites'] );
		$this->assertSame( 0, $result['counts']['schedule_management']['rollback_apply_advertised_sites'] );
		$this->assertStringContainsString( 'schedule_management', $encoded );
		$this->assertStringContainsString( 'rollback_preview_supported_sites', $encoded );
		$this->assertStringNotContainsString( 'client1.example.com', $encoded );
		$this->assertStringNotContainsString( 'Client 1', $encoded );
		$this->assertStringNotContainsString( 'alynt_scan_upload', $encoded );
	}

	/**
	 * Cleanup-preview diagnostics are aggregate-only.
	 *
	 * @return void
	 */
	public function test_cleanup_preview_diagnostics_are_aggregate_only() {
		$diagnostics = new Alynt_Drime_Backups_Dashboard_Diagnostics(
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Site_Repository(
				array(
					$this->site( 1 ),
					$this->site( 2 ),
				)
			),
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Snapshot_Repository(
				array(
					1 => $this->snapshot(
						array(
							'remote_actions' => array(
								'protocol_version'   => 2,
								'enabled'            => true,
								'cleanup_management' => array(
									'protocol_version'        => 2,
									'capability_version'      => 1,
									'enabled'                 => true,
									'preview_supported'       => true,
									'apply_supported'         => false,
									'scope'                   => 'safe_local_uploader_owned',
									'supported_categories'    => array( 'uploader_temp_artifacts' ),
									'max_preview_age_seconds' => 900,
								),
							),
						)
					),
					2 => $this->snapshot(
						array(
							'remote_actions' => array(
								'protocol_version'   => 2,
								'enabled'            => true,
								'cleanup_management' => array(
									'protocol_version'       => 2,
									'enabled'                => true,
									'preview_supported'      => true,
									'apply_supported'        => true,
									'cleanup_apply_available' => true,
									'scope'                  => 'safe_local_uploader_owned',
									'supported_categories'   => array( 'uploader_temp_artifacts' ),
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

		$this->assertSame( 2, $result['counts']['cleanup_preview']['reporting_sites'] );
		$this->assertSame( 1, $result['counts']['cleanup_preview']['preview_supported_sites'] );
		$this->assertSame( 1, $result['counts']['cleanup_preview']['unavailable_sites'] );
		$this->assertSame( 1, $result['counts']['cleanup_preview']['apply_or_mutation_advertised_sites'] );
		$this->assertSame( 2, $result['counts']['cleanup_preview']['supported_categories'] );
		$this->assertStringContainsString( 'cleanup_preview', $encoded );
		$this->assertStringContainsString( 'preview_supported_sites', $encoded );
		$this->assertStringNotContainsString( 'client1.example.com', $encoded );
		$this->assertStringNotContainsString( 'Client 1', $encoded );
		$this->assertStringNotContainsString( 'uploader_temp_artifacts', $encoded );
	}

	/**
	 * Restore-readiness diagnostics are aggregate-only and evidence-only.
	 *
	 * @return void
	 */
	public function test_restore_readiness_diagnostics_are_aggregate_only() {
		$diagnostics = new Alynt_Drime_Backups_Dashboard_Diagnostics(
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Site_Repository(
				array(
					$this->site( 1 ),
					$this->site( 2 ),
					$this->site( 3 ),
				)
			),
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Snapshot_Repository(
				array(
					1 => $this->snapshot(
						array(
							'restore_readiness' => array(
								'overall_state' => 'evidence_available',
								'candidates'    => array(
									array(
										'source'          => 'server',
										'candidate_ref'   => 'opaque-do-not-export',
										'component_state' => 'complete',
										'checksum_state'  => 'verified',
										'manifest_state'  => 'compatible',
										'sidecar_state'   => 'present',
									),
									array(
										'source'          => 'wpvivid',
										'candidate_ref'   => 'opaque-do-not-export-2',
										'component_state' => 'unknown',
										'checksum_state'  => 'not_reported',
										'manifest_state'  => 'not_reported',
										'sidecar_state'   => 'not_reported',
									),
								),
							),
						)
					),
					2 => $this->snapshot(),
					3 => $this->snapshot(
						array(
							'restore_readiness' => array(
								'overall_state' => 'incomplete',
								'candidates'    => array(
									array(
										'source'          => 'server',
										'candidate_ref'   => 'another-opaque-ref',
										'component_state' => 'partial',
										'checksum_state'  => 'unknown',
										'manifest_state'  => 'unknown',
										'sidecar_state'   => 'missing',
									),
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

		$this->assertSame( 2, $result['counts']['restore_readiness']['reporting_sites'] );
		$this->assertSame( 1, $result['counts']['restore_readiness']['evidence_sites'] );
		$this->assertSame( 1, $result['counts']['restore_readiness']['incomplete_sites'] );
		$this->assertSame( 3, $result['counts']['restore_readiness']['reported_candidates'] );
		$this->assertSame( 1, $result['counts']['restore_readiness']['complete_candidates'] );
		$this->assertSame( 2, $result['counts']['restore_readiness']['incomplete_candidates'] );
		$this->assertSame( 2, $result['counts']['restore_readiness']['server_candidates'] );
		$this->assertSame( 1, $result['counts']['restore_readiness']['server_complete'] );
		$this->assertSame( 1, $result['counts']['restore_readiness']['server_incomplete'] );
		$this->assertSame( 1, $result['counts']['restore_readiness']['wpvivid_candidates'] );
		$this->assertSame( 0, $result['counts']['restore_readiness']['wpvivid_complete'] );
		$this->assertSame( 1, $result['counts']['restore_readiness']['wpvivid_incomplete'] );
		$this->assertStringContainsString( 'restore_readiness', $encoded );
		$this->assertStringContainsString( 'reported_candidates', $encoded );
		$this->assertStringContainsString( 'server_candidates', $encoded );
		$this->assertStringContainsString( 'wpvivid_candidates', $encoded );
		$this->assertStringNotContainsString( 'client1.example.com', $encoded );
		$this->assertStringNotContainsString( 'Client 1', $encoded );
		$this->assertStringNotContainsString( 'opaque-do-not-export', $encoded );
		$this->assertStringNotContainsString( 'another-opaque-ref', $encoded );
	}

}