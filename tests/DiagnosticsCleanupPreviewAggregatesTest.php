<?php
/**
 * Diagnostics cleanup-preview aggregate tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/diagnostics-test-bootstrap.php';

/**
 * Tests support-safe cleanup-preview aggregate diagnostics.
 */
class DiagnosticsCleanupPreviewAggregatesTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Test_Fixtures;

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
								'protocol_version'        => 2,
								'enabled'                 => true,
								'cleanup_management'      => array(
									'protocol_version'         => 2,
									'enabled'                  => true,
									'preview_supported'        => true,
									'apply_supported'          => true,
									'cleanup_apply_available'  => true,
									'scope'                    => 'safe_local_uploader_owned',
									'supported_categories'     => array( 'uploader_temp_artifacts' ),
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
}
