<?php
/**
 * Status payload validator backup source hardening tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/status-payload-validator-test-bootstrap.php';

/**
 * Tests backup source bounds and forbidden-field rejection.
 */
class StatusPayloadValidatorBackupSourceHardeningTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Status_Payload_Validator_Test_Fixtures;

	/**
	 * Source-level labels, warnings, and enum values are bounded before storage.
	 *
	 * @return void
	 */
	public function test_backup_source_bounds_warning_volume_and_unknown_statuses() {
		$warnings = array();

		for ( $index = 0; $index < 12; $index++ ) {
			$warnings[] = array(
				'code'    => 'warning_' . $index,
				'message' => 'Warning ' . $index,
			);
		}

		$result = $this->validate_payload(
			array_merge(
				$this->payload(),
				array(
					'backup_sources' => array(
						'server' => $this->source_payload(
							array(
								'source_label'              => str_repeat( 'S', 120 ),
								'latest_remote_status'      => 'uploaded_elsewhere',
								'latest_inventory_evidence' => 'raw_drime_api',
								'source_activity_evidence'  => 'raw_log_path',
								'freshness_status'          => 'mysterious',
								'warnings'                  => $warnings,
							)
						),
					),
				)
			)
		);

		$this->assertIsArray( $result );
		$this->assertSame( 80, strlen( $result['backup_sources']['server']['source_label'] ) );
		$this->assertSame( '', $result['backup_sources']['server']['latest_remote_status'] );
		$this->assertSame( '', $result['backup_sources']['server']['latest_inventory_evidence'] );
		$this->assertSame( '', $result['backup_sources']['server']['source_activity_evidence'] );
		$this->assertSame( '', $result['backup_sources']['server']['freshness_status'] );
		$this->assertSame( 10, $result['backup_sources']['server']['warning_count'] );
		$this->assertCount( 10, $result['backup_sources']['server']['warnings'] );
	}

	/**
	 * Forbidden nested source fields are rejected instead of silently stored.
	 *
	 * @return void
	 */
	public function test_forbidden_nested_backup_source_field_is_rejected() {
		$result = $this->validate_payload(
			array_merge(
				$this->payload(),
				array(
					'backup_sources' => array(
						'server' => $this->source_payload(
							array(
								'remote_index_path' => '/var/backups/private.remote-index.json',
							)
						),
					),
				)
			)
		);

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'payload_invalid', $result->get_error_code() );
	}
}
