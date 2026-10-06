<?php
/**
 * Status payload validator backup source tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/status-payload-validator-test-bootstrap.php';

/**
 * Tests client status payload backup source validation.
 */
class StatusPayloadValidatorBackupSourcesTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Status_Payload_Validator_Test_Fixtures;

	/**
	 * Optional backup source summaries are allowlisted and sanitized.
	 *
	 * @return void
	 */
	public function test_backup_sources_are_allowlisted_and_sanitized() {
		$validator = $this->status_payload_validator();
		$result    = $validator->validate(
			array_merge(
				$this->payload(),
				array(
					'backup_sources' => array(
						'server'      => array_merge(
							$this->source_payload(),
							array(
								'source_label' => '<b>Server runner</b>',
								'extra_field'  => 'ignored',
							)
						),
						'wpvivid'     => array_merge(
							$this->source_payload(),
							array(
								'source_key'       => 'wpvivid',
								'freshness_status' => 'fresh',
								'schedule_policy'  => array(
									'detected'              => true,
									'basis'                 => 'wpvivid_schedule_addon_setting',
									'recurrence'            => 'wpvivid_weekly',
									'schedule_count'        => 1,
									'interval_seconds'      => 604800,
									'grace_seconds'         => 172800,
									'policy_window_seconds' => 777600,
									'raw_option'            => 'ignored',
								),
							)
						),
						'unsupported' => array(
							'configured' => true,
						),
					),
				)
			),
			'11111111-1111-4111-8111-111111111111'
		);

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'backup_sources', $result );
		$this->assertArrayHasKey( 'server', $result['backup_sources'] );
		$this->assertArrayHasKey( 'wpvivid', $result['backup_sources'] );
		$this->assertArrayNotHasKey( 'unsupported', $result['backup_sources'] );
		$this->assertArrayNotHasKey( 'extra_field', $result['backup_sources']['server'] );
		$this->assertSame( 'server', $result['backup_sources']['server']['source_key'] );
		$this->assertSame( '<b>Server runner</b>', $result['backup_sources']['server']['source_label'] );
		$this->assertSame( 3, $result['backup_sources']['server']['latest_inventory_count'] );
		$this->assertSame( 'stale', $result['backup_sources']['server']['freshness_status'] );
		$this->assertSame( 1, $result['backup_sources']['server']['warning_count'] );
		$this->assertSame( 'wpvivid_backup_log', $result['backup_sources']['wpvivid']['source_activity_evidence'] );
		$this->assertSame( 0, $result['backup_sources']['wpvivid']['local_candidate_count'] );
		$this->assertSame( 'wpvivid_schedule_addon_setting', $result['backup_sources']['wpvivid']['schedule_policy']['basis'] );
		$this->assertSame( 777600, $result['backup_sources']['wpvivid']['schedule_policy']['policy_window_seconds'] );
		$this->assertArrayNotHasKey( 'raw_option', $result['backup_sources']['wpvivid']['schedule_policy'] );
	}

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

		$validator = $this->status_payload_validator();
		$result    = $validator->validate(
			array_merge(
				$this->payload(),
				array(
					'backup_sources' => array(
						'server' => array_merge(
							$this->source_payload(),
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
			),
			'11111111-1111-4111-8111-111111111111'
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
		$validator = $this->status_payload_validator();
		$result    = $validator->validate(
			array_merge(
				$this->payload(),
				array(
					'backup_sources' => array(
						'server' => array_merge(
							$this->source_payload(),
							array(
								'remote_index_path' => '/var/backups/private.remote-index.json',
							)
						),
					),
				)
			),
			'11111111-1111-4111-8111-111111111111'
		);

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'payload_invalid', $result->get_error_code() );
	}
}
