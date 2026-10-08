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
	use Alynt_Drime_Backups_Dashboard_Status_Payload_Validator_Backup_Source_Fixtures;

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
}
