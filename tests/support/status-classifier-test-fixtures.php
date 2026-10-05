<?php
/**
 * Status classifier test fixture builders.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared fixture builders for status classifier tests.
 */
trait Alynt_Drime_Backups_Dashboard_Status_Classifier_Test_Fixtures {
	/**
	 * Builds an active site row.
	 *
	 * @param string $last_seen Last seen date.
	 * @return array<string,mixed>
	 */
	private function active_site( $last_seen = '2023-11-14 22:15:00' ) {
		return array(
			'status'       => 'working',
			'paused_at'    => null,
			'last_seen_at' => $last_seen,
		);
	}

	/**
	 * Builds a snapshot row.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @param string              $captured_at Captured date.
	 * @return array<string,mixed>
	 */
	private function snapshot( array $payload, $captured_at = '2023-11-14 22:15:00' ) {
		return array(
			'schema_version'  => isset( $payload['schema_version'] ) ? $payload['schema_version'] : 1,
			'decoded_payload' => $payload,
			'captured_at'     => $captured_at,
		);
	}

	/**
	 * Builds a healthy payload.
	 *
	 * @return array<string,mixed>
	 */
	private function healthy_payload() {
		return array(
			'schema_version'              => 1,
			'server_outbox_configured'    => true,
			'wpvivid_override_configured' => false,
			'old_wpvivid_uploader_active' => false,
			'failed_count'                => 0,
			'warning_count'               => 0,
			'warnings'                    => array(),
			'cron_status'                 => 'ok',
		);
	}

	/**
	 * Builds a source summary payload.
	 *
	 * @return array<string,mixed>
	 */
	private function source_payload() {
		return array(
			'source_key'                => 'server',
			'source_label'              => 'Server',
			'configured'                => true,
			'has_upload_evidence'       => true,
			'queued_count'              => 0,
			'uploaded_count'            => 1,
			'failed_count'              => 0,
			'remote_registry_count'     => 1,
			'latest_uploaded_at'        => 1700000000,
			'latest_inventory_count'    => 1,
			'latest_inventory_evidence' => 'local_upload_registry',
			'freshness_status'          => 'fresh',
			'warning_count'             => 0,
			'warnings'                  => array(),
		);
	}
}
