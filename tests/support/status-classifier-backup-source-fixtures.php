<?php
/**
 * Status classifier backup-source fixture builders.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared backup-source fixture builders for status classifier tests.
 */
trait Alynt_Drime_Backups_Dashboard_Status_Classifier_Backup_Source_Fixtures {
	/**
	 * Builds a source summary payload.
	 *
	 * @param array<string,mixed> $overrides Source overrides.
	 * @return array<string,mixed>
	 */
	private function source_payload( array $overrides = array() ) {
		return array_merge(
			array(
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
			),
			$overrides
		);
	}

	/**
	 * Builds a backup sources payload with server and WPvivid source summaries.
	 *
	 * @param array<string,mixed> $wpvivid_overrides WPvivid source overrides.
	 * @param array<string,mixed> $server_overrides Server source overrides.
	 * @return array<string,array<string,mixed>>
	 */
	private function backup_sources_payload( array $wpvivid_overrides = array(), array $server_overrides = array() ) {
		return array(
			'server'  => $this->source_payload( $server_overrides ),
			'wpvivid' => $this->wpvivid_source_payload( $wpvivid_overrides ),
		);
	}

	/**
	 * Builds a WPvivid source summary payload.
	 *
	 * @param array<string,mixed> $overrides Source overrides.
	 * @return array<string,mixed>
	 */
	private function wpvivid_source_payload( array $overrides = array() ) {
		return $this->source_payload(
			array_merge(
				array(
					'source_key'   => 'wpvivid',
					'source_label' => 'WPvivid',
				),
				$overrides
			)
		);
	}
}
