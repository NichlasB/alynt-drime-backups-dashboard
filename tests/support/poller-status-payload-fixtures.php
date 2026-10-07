<?php
/**
 * Status payload fixtures for poller tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Builds status payload and HTTP response fixtures for poller tests.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Poller_Status_Payload_Fixtures {
	/**
	 * Creates a valid status payload.
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
	 * Creates a successful status HTTP client fixture.
	 *
	 * @param array<string,mixed> $payload_overrides Payload overrides.
	 * @return callable
	 */
	private function successful_http_client( array $payload_overrides = array() ) {
		return function () use ( $payload_overrides ) {
			return array(
				'response' => array(
					'code' => 200,
				),
				'body'     => wp_json_encode(
					array_merge(
						$this->payload(),
						$payload_overrides
					)
				),
			);
		};
	}
}
