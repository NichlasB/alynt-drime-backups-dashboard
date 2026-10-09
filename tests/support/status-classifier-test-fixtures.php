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
	use Alynt_Drime_Backups_Dashboard_Status_Classifier_Backup_Source_Fixtures;

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
	 * Classifies an active-site snapshot payload at the default fixture time.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @return array<string,mixed>
	 */
	private function classify_payload( array $payload ) {
		return $this->classify_snapshot( $this->snapshot( $payload ) );
	}

	/**
	 * Classifies an active-site snapshot row at the default fixture time.
	 *
	 * @param array<string,mixed> $snapshot Snapshot row.
	 * @return array<string,mixed>
	 */
	private function classify_snapshot( array $snapshot ) {
		return $this->classify_site_snapshot(
			$this->active_site(),
			$snapshot,
			1700000300
		);
	}

	/**
	 * Classifies a site and snapshot row at a fixture time.
	 *
	 * @param array<string,mixed>      $site Site row.
	 * @param array<string,mixed>|null $snapshot Snapshot row.
	 * @param int                      $now Current timestamp.
	 * @return array<string,mixed>
	 */
	private function classify_site_snapshot( array $site, $snapshot, $now ) {
		return $this->classifier->classify(
			$site,
			$snapshot,
			$now
		);
	}

}
