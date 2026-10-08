<?php
/**
 * Status payload validator backup source fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared status payload validator backup source fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Status_Payload_Validator_Backup_Source_Fixtures {
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
