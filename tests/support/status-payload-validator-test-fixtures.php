<?php
/**
 * Status payload validator test fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared status payload validator fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Status_Payload_Validator_Test_Fixtures {
	/**
	 * Creates a fresh validator under test.
	 *
	 * @return Alynt_Drime_Backups_Dashboard_Status_Payload_Validator
	 */
	private function status_payload_validator() {
		return new Alynt_Drime_Backups_Dashboard_Status_Payload_Validator();
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
			'source_key'                            => 'server',
			'source_label'                          => 'Server',
			'configured'                            => true,
			'has_upload_evidence'                   => true,
			'queued_count'                          => 1,
			'uploaded_count'                        => 2,
			'failed_count'                          => 0,
			'remote_registry_count'                 => 1,
			'latest_created_at'                     => 1786305000,
			'latest_uploaded_at'                    => 1786305600,
			'latest_upload_age_seconds'             => 3600,
			'latest_remote_status'                  => 'uploaded',
			'latest_inventory_count'                => 3,
			'latest_inventory_evidence'             => 'generic_outbox_remote_catalog',
			'latest_source_activity_at'             => 1786305900,
			'latest_source_activity_age_seconds'     => 3300,
			'source_activity_evidence'              => 'wpvivid_backup_log',
			'local_candidate_count'                 => 0,
			'freshness_status'                      => 'stale',
			'freshness_window_seconds'              => 129600,
			'warning_count'                         => 1,
			'warnings'                              => array(
				array(
					'code'    => 'source_latest_upload_stale',
					'message' => 'The latest uploaded backup evidence is older than the default freshness window.',
				),
			),
		);
	}
}