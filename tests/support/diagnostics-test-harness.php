<?php
/**
 * Test support for diagnostics tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/diagnostics-support-summary-test-harness.php';

/**
 * Shared fixture builders for diagnostics tests.
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Test_Fixtures {
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

	/**
	 * Creates a healthy snapshot.
	 *
	 * @param array<string,mixed> $overrides Payload overrides.
	 * @return array<string,mixed>
	 */
	private function snapshot( array $overrides = array() ) {
		return array(
			'schema_version'   => 1,
			'observed_at'      => gmdate( 'Y-m-d H:i:s' ),
			'decoded_payload'  => array_merge(
				array(
					'schema_version'           => 1,
					'server_outbox_configured' => true,
					'failed_count'             => 0,
					'warning_count'            => 0,
					'warnings'                 => array(),
					'cron_status'              => 'ok',
				),
				$overrides
			),
		);
	}

	/**
	 * Creates a retained snapshot summary row.
	 *
	 * @param string $status Snapshot status.
	 * @param string $observed_at Observed time.
	 * @return array<string,mixed>
	 */
	private function snapshot_row( $status, $observed_at ) {
		return array(
			'overall_status' => $status,
			'observed_at'    => $observed_at,
		);
	}

	/**
	 * Collects diagnostics for focused site/snapshot fixtures.
	 *
	 * @param array<int,array<string,mixed>> $sites Sites.
	 * @param array<int,array<string,mixed>> $snapshots Latest snapshots keyed by site ID.
	 * @param array<int,array<int,array<string,mixed>>> $histories Recent snapshot histories keyed by site ID.
	 * @param array<int,int> $action_counts Retained action counts keyed by site ID.
	 * @param array<int,int> $non_terminal_action_counts Non-terminal action counts keyed by site ID.
	 * @return array<string,mixed>
	 */
	private function collect_diagnostics( array $sites, array $snapshots = array(), array $histories = array(), array $action_counts = array(), array $non_terminal_action_counts = array() ) {
		$diagnostics = new Alynt_Drime_Backups_Dashboard_Diagnostics(
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Site_Repository( $sites ),
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Snapshot_Repository( $snapshots, $histories ),
			new Alynt_Drime_Backups_Dashboard_Status_Classifier(),
			null,
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Remote_Action_Repository( $action_counts, $non_terminal_action_counts )
		);

		return $diagnostics->collect();
	}
}
