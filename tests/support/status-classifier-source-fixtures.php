<?php
/**
 * Status classifier source fixture builders.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared source fixture builders for status classifier tests.
 */
trait Alynt_Drime_Backups_Dashboard_Status_Classifier_Source_Fixtures {
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
}
