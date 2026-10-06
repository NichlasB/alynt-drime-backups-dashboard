<?php
/**
 * Poller test fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Builds poller collaborators, site rows, and status payloads for tests.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Poller_Fixtures {
	/**
	 * Creates a deterministic credential vault for poller tests.
	 *
	 * @return Alynt_Drime_Backups_Dashboard_Credential_Vault
	 */
	private function vault() {
		return new Alynt_Drime_Backups_Dashboard_Credential_Vault( str_repeat( 'k', 64 ) );
	}

	/**
	 * Creates a poller.
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Test_Poller_Site_Repository     $sites Sites.
	 * @param Alynt_Drime_Backups_Dashboard_Test_Poller_Snapshot_Repository $snapshots Snapshots.
	 * @param Alynt_Drime_Backups_Dashboard_Credential_Vault                $vault Vault.
	 * @param callable                                                      $http_client HTTP client.
	 * @return Alynt_Drime_Backups_Dashboard_Poller
	 */
	private function poller( $sites, $snapshots, $vault, $http_client ) {
		return new Alynt_Drime_Backups_Dashboard_Poller(
			$sites,
			$snapshots,
			new Alynt_Drime_Backups_Dashboard_Status_Classifier(),
			$vault,
			new Alynt_Drime_Backups_Dashboard_Safe_Transport(
				new Alynt_Drime_Backups_Dashboard_Origin_Validator(),
				function () {
					return array( '93.184.216.34' );
				}
			),
			new Alynt_Drime_Backups_Dashboard_Status_Payload_Validator(),
			$http_client,
			null,
			new Alynt_Drime_Backups_Dashboard_Test_Poller_Remote_Action_Reconciler()
		);
	}

	/**
	 * Creates a dashboard site row.
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Credential_Vault $vault Vault.
	 * @return array<string,mixed>
	 */
	private function site( $vault, $overrides = array() ) {
		$public_id = isset( $overrides['public_id'] ) ? (string) $overrides['public_id'] : '00000000-0000-4000-8000-000000000000';
		$site      = array(
			'id'                         => 77,
			'public_id'                  => $public_id,
			'expected_origin'            => 'https://client.example.com',
			'site_uuid'                  => '11111111-1111-4111-8111-111111111111',
			'polling_key_id'             => 'pk_example_0000000000000000',
			'polling_secret_ciphertext'  => $vault->encrypt( str_repeat( 'S', 43 ), 'site:' . $public_id ),
			'enrollment_status'          => 'awaiting_first_poll',
			'overall_status'             => 'pending',
			'consecutive_failures'       => 0,
		);

		return array_merge( $site, $overrides );
	}

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
