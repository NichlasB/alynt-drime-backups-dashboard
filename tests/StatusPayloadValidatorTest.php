<?php
/**
 * Status payload validator tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/includes/class-status-payload-validator.php';

/**
 * Tests client status payload validation.
 */
class StatusPayloadValidatorTest extends TestCase {
	/**
	 * Valid schema-1 payload is allowlisted and sanitized.
	 *
	 * @return void
	 */
	public function test_valid_payload_is_allowlisted() {
		$validator = new Alynt_Drime_Backups_Dashboard_Status_Payload_Validator();
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
		$validator = new Alynt_Drime_Backups_Dashboard_Status_Payload_Validator();
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

		$validator = new Alynt_Drime_Backups_Dashboard_Status_Payload_Validator();
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
		$validator = new Alynt_Drime_Backups_Dashboard_Status_Payload_Validator();
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
		$validator = new Alynt_Drime_Backups_Dashboard_Status_Payload_Validator();
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
		$validator = new Alynt_Drime_Backups_Dashboard_Status_Payload_Validator();
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
		$validator = new Alynt_Drime_Backups_Dashboard_Status_Payload_Validator();
		$result    = $validator->validate(
			$this->payload(),
			'22222222-2222-4222-8222-222222222222'
		);

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'site_uuid_mismatch', $result->get_error_code() );
	}

	/**
	 * Creates a valid payload.
	 *
	 * @return array<string,mixed>
	 */
	private function payload() {
		return array(
			'schema_version'              => 1,
			'site_uuid'                   => '11111111-1111-4111-8111-111111111111',
			'plugin_version'              => '0.5.3',
			'queue_count'                 => 0,
			'uploaded_count'              => 1,
			'failed_count'                => 0,
			'active_upload'               => false,
			'auto_scan_enabled'           => true,
			'server_cron_expected'        => false,
			'server_outbox_configured'    => true,
			'server_outbox_readable'      => true,
			'wpvivid_override_configured' => false,
			'old_wpvivid_uploader_active' => false,
			'wp_cron_disabled'            => false,
			'cron_status'                 => 'ok',
			'cron_reason'                 => 'Scheduled scans are available.',
			'warning_count'               => 0,
			'warnings'                    => array(),
			'last_runner'                 => 'wp_cron',
			'last_runner_at'              => 1786305600,
			'last_scheduled_scan_at'      => 1786305600,
			'last_wp_cli_scan_at'         => 0,
		);
	}

	/**
	 * Creates a backup source payload.
	 *
	 * @return array<string,mixed>
	 */
	private function source_payload() {
		return array(
			'source_key'                => 'server',
			'source_label'              => 'Server',
			'configured'                => true,
			'has_upload_evidence'       => true,
			'queued_count'              => 1,
			'uploaded_count'            => 2,
			'failed_count'              => 0,
			'remote_registry_count'     => 1,
			'latest_created_at'         => 1786305000,
			'latest_uploaded_at'        => 1786305600,
			'latest_upload_age_seconds' => 3600,
			'latest_remote_status'      => 'uploaded',
			'latest_inventory_count'    => 3,
			'latest_inventory_evidence' => 'generic_outbox_remote_catalog',
			'freshness_status'          => 'stale',
			'freshness_window_seconds'  => 129600,
			'warning_count'             => 1,
			'warnings'                  => array(
				array(
					'code'    => 'source_latest_upload_stale',
					'message' => 'The latest uploaded backup evidence is older than the default freshness window.',
				),
			),
		);
	}
}
