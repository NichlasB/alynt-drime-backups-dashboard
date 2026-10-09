<?php
/**
 * Core diagnostics fixture builders.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/diagnostics-snapshot-fixtures.php';

/**
 * Shared core fixture builders for diagnostics tests.
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Core_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Snapshot_Fixtures;

	/**
	 * Creates a site row.
	 *
	 * @param int                 $site_id Site ID.
	 * @param array<string,mixed> $overrides Overrides.
	 * @return array<string,mixed>
	 */
	private function site( $site_id, array $overrides = array() ) {
		return array_merge(
			array(
				'id'                         => $site_id,
				'public_id'                  => '00000000-0000-4000-8000-00000000000' . $site_id,
				'site_label'                 => 'Client ' . $site_id,
				'expected_origin'            => 'https://client' . $site_id . '.example.com',
				'enrollment_status'          => 'active',
				'overall_status'             => 'working',
				'polling_key_id'             => 'pk_example_0000000000000000',
				'polling_secret_ciphertext'  => 'adbv1.ciphertext',
				'next_poll_at'               => '2099-01-01 00:00:00',
				'last_poll_attempt_at'       => '',
				'last_seen_at'               => '',
				'consecutive_failures'       => 0,
				'last_error_code'            => '',
				'last_error_summary'         => '',
				'paused_at'                  => '',
			),
			$overrides
		);
	}
}
