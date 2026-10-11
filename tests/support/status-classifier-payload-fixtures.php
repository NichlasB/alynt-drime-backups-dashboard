<?php
/**
 * Status classifier payload fixture builders.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared payload builders for status classifier tests.
 */
trait Alynt_Drime_Backups_Dashboard_Status_Classifier_Payload_Fixtures {
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
}
