<?php
/**
 * Status payload validator tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/includes/traits/trait-status-payload-validator-backup-sources.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-status-payload-validator-restore-readiness.php';
require_once dirname( __DIR__ ) . '/includes/traits/trait-status-payload-validator-sanitizers.php';
require_once dirname( __DIR__ ) . '/includes/class-status-payload-validator.php';
require_once __DIR__ . '/support/status-payload-validator-test-fixtures.php';

/**
 * Tests client status payload validation.
 */
class StatusPayloadValidatorTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Status_Payload_Validator_Test_Fixtures;

	/**
	 * Valid schema-1 payload is allowlisted and sanitized.
	 *
	 * @return void
	 */
	public function test_valid_payload_is_allowlisted() {
		$validator = $this->status_payload_validator();
		$result    = $validator->validate(
			array_merge(
				$this->payload(),
				array(
					'unexpected_future_field' => 'ignored',
				)
			),
			'11111111-1111-4111-8111-111111111111'
		);

		$this->assertIsArray( $result );
		$this->assertSame( 1, $result['schema_version'] );
		$this->assertSame( '11111111-1111-4111-8111-111111111111', $result['site_uuid'] );
		$this->assertArrayNotHasKey( 'unexpected_future_field', $result );
		$this->assertArrayNotHasKey( 'backup_sources', $result );
	}

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
	 * Optional remote-action capabilities are allowlisted and sanitized.
	 *
	 * @return void
	 */
	public function test_remote_action_capabilities_are_allowlisted_and_sanitized() {
		$validator = $this->status_payload_validator();
		$result    = $validator->validate(
			array_merge(
				$this->payload(),
				array(
					'remote_actions' => array(
						'protocol_version'            => 2,
						'enabled'                     => true,
						'key_id'                      => 'ak_123',
						'allowed_actions'             => array( 'scan_upload_now', 'restore_now' ),
						'sodium_available'            => true,
						'min_interval_seconds'        => 600,
						'one_running_action_per_site' => true,
						'schedule_management'         => array(
							'protocol_version'   => 2,
							'capability_version' => 1,
							'enabled'            => true,
							'preview_only'       => true,
							'apply_supported'    => false,
							'rollback_supported' => false,
							'schedules'          => array(
								array(
									'schedule_id'              => 'alynt_scan_upload',
									'label'                    => 'Alynt scan/upload',
									'owner'                    => 'alynt_uploader',
									'manageable'               => true,
									'current_cadence'          => 'every_15_minutes',
									'current_interval_seconds' => 900,
									'current_next_run_at'      => '2026-06-25T16:45:00+00:00',
									'supported_cadences'       => array( 'every_15_minutes' ),
									'minimum_interval_seconds' => 900,
									'extra_field'              => 'ignored',
								),
							),
						),
						'extra_field'                 => 'ignored',
					),
				)
			),
			'11111111-1111-4111-8111-111111111111'
		);

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'remote_actions', $result );
		$this->assertTrue( $result['remote_actions']['enabled'] );
		$this->assertSame( array( 'scan_upload_now' ), $result['remote_actions']['allowed_actions'] );
		$this->assertArrayNotHasKey( 'extra_field', $result['remote_actions'] );
		$this->assertArrayHasKey( 'schedule_management', $result['remote_actions'] );
		$this->assertTrue( $result['remote_actions']['schedule_management']['enabled'] );
		$this->assertTrue( $result['remote_actions']['schedule_management']['preview_only'] );
		$this->assertFalse( $result['remote_actions']['schedule_management']['apply_supported'] );
		$this->assertFalse( $result['remote_actions']['schedule_management']['rollback_supported'] );
		$this->assertSame( 'alynt_scan_upload', $result['remote_actions']['schedule_management']['schedules'][0]['schedule_id'] );
		$this->assertArrayNotHasKey( 'extra_field', $result['remote_actions']['schedule_management']['schedules'][0] );
	}

	/**
	 * Optional restore-readiness evidence is allowlisted and sanitized.
	 *
	 * @return void
	 */
	public function test_restore_readiness_is_allowlisted_and_sanitized() {
		$validator = $this->status_payload_validator();
		$result    = $validator->validate(
			array_merge(
				$this->payload(),
				array(
					'restore_readiness' => array(
						'schema_version' => 1,
						'generated_at'   => '2026-10-02T12:00:00Z',
						'overall_state'  => 'evidence_available',
						'extra_field'    => 'ignored',
						'candidates'     => array(
							array(
								'source'                    => 'server',
								'candidate_ref'             => 'opaque-client-ref_123',
								'latest_backup_finished_at' => '2026-10-02T01:30:00Z',
								'component_state'           => 'complete',
								'checksum_state'            => 'verified',
								'manifest_state'            => 'compatible',
								'sidecar_state'             => 'present',
								'age_seconds'               => 3600,
								'warnings'                  => array( 'restore_evidence_incomplete' ),
								'raw_name'                  => 'ignored',
							),
							array(
								'source'          => 'wpvivid',
								'candidate_ref'   => '../private/backup.zip',
								'component_state' => 'surprising',
								'checksum_state'  => 'not_reported',
								'manifest_state'  => 'not_reported',
								'sidecar_state'   => 'not_reported',
							),
							array(
								'source' => 'unsupported',
							),
						),
					),
				)
			),
			'11111111-1111-4111-8111-111111111111'
		);

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'restore_readiness', $result );
		$this->assertSame( 'evidence_available', $result['restore_readiness']['overall_state'] );
		$this->assertArrayNotHasKey( 'extra_field', $result['restore_readiness'] );
		$this->assertCount( 2, $result['restore_readiness']['candidates'] );
		$this->assertSame( 'server', $result['restore_readiness']['candidates'][0]['source'] );
		$this->assertSame( 'opaque-client-ref_123', $result['restore_readiness']['candidates'][0]['candidate_ref'] );
		$this->assertSame( 'verified', $result['restore_readiness']['candidates'][0]['checksum_state'] );
		$this->assertSame( array( 'restore_evidence_incomplete' ), $result['restore_readiness']['candidates'][0]['warnings'] );
		$this->assertSame( '', $result['restore_readiness']['candidates'][1]['candidate_ref'] );
		$this->assertSame( 'unknown', $result['restore_readiness']['candidates'][1]['component_state'] );
		$this->assertArrayNotHasKey( 'raw_name', $result['restore_readiness']['candidates'][0] );
	}

	/**
	 * Remote-action summaries containing secrets or path-mode fields are rejected.
	 *
	 * @return void
	 */
	public function test_remote_action_forbidden_field_is_rejected() {
		$validator = $this->status_payload_validator();
		$result    = $validator->validate(
			array_merge(
				$this->payload(),
				array(
					'remote_actions' => array(
						'protocol_version' => 2,
						'enabled'          => true,
						'last_action'      => array(
							'action_id' => '11111111-1111-4111-8111-111111111111',
							'file'      => '/private/backup.zip',
						),
					),
				)
			),
			'11111111-1111-4111-8111-111111111111'
		);

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'payload_invalid', $result->get_error_code() );
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

	/**
	 * Overlong plugin versions are bounded before fixed-width storage.
	 *
	 * @return void
	 */
	public function test_overlong_plugin_version_is_bounded() {
		$validator = $this->status_payload_validator();
		$result    = $validator->validate(
			array_merge(
				$this->payload(),
				array(
					'plugin_version' => str_repeat( '9', 100 ),
				)
			),
			'11111111-1111-4111-8111-111111111111'
		);

		$this->assertIsArray( $result );
		$this->assertSame( 64, strlen( $result['plugin_version'] ) );
	}

	/**
	 * Path-mode fields are rejected.
	 *
	 * @return void
	 */
	public function test_forbidden_path_field_is_rejected() {
		$validator = $this->status_payload_validator();
		$result    = $validator->validate(
			array_merge(
				$this->payload(),
				array(
					'server_outbox_path' => '/var/www/site/private/backups',
				)
			),
			'11111111-1111-4111-8111-111111111111'
		);

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'payload_invalid', $result->get_error_code() );
	}

	/**
	 * UUID mismatch is rejected.
	 *
	 * @return void
	 */
	public function test_site_uuid_mismatch_is_rejected() {
		$validator = $this->status_payload_validator();
		$result    = $validator->validate(
			$this->payload(),
			'22222222-2222-4222-8222-222222222222'
		);

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'site_uuid_mismatch', $result->get_error_code() );
	}

}
