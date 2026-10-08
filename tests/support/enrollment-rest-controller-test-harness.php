<?php
/**
 * Enrollment REST controller test harness.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/enrollment-rest-controller-repository-double.php';
require_once __DIR__ . '/enrollment-rest-controller-transient-shims.php';
require_once __DIR__ . '/enrollment-rest-controller-request-fixtures.php';

/**
 * Shared enrollment REST controller fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Enrollment_REST_Controller_Test_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Enrollment_REST_Request_Fixtures;

	/**
	 * Creates the controller.
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Test_Enrollment_REST_Repository $repository Repository.
	 * @param Alynt_Drime_Backups_Dashboard_Credential_Vault|null          $vault Vault.
	 * @return Alynt_Drime_Backups_Dashboard_Enrollment_REST_Controller
	 */
	private function controller( $repository, $vault = null ) {
		return new Alynt_Drime_Backups_Dashboard_Enrollment_REST_Controller(
			$repository,
			new Alynt_Drime_Backups_Dashboard_Origin_Validator(),
			$vault instanceof Alynt_Drime_Backups_Dashboard_Credential_Vault ? $vault : new Alynt_Drime_Backups_Dashboard_Credential_Vault( str_repeat( 'k', 64 ) )
		);
	}

	/**
	 * Creates a pending site row.
	 *
	 * @param string $secret Plain pairing secret.
	 * @param string $expires_at Expiry.
	 * @return array<string,mixed>
	 */
	private function pending_site( $secret, $expires_at = '2099-01-01 00:15:00' ) {
		return array(
			'id'                  => 77,
			'public_id'           => '00000000-0000-4000-8000-000000000000',
			'expected_origin'     => 'https://client.example.com',
			'enrollment_status'   => 'pending',
			'pairing_secret_hash' => Alynt_Drime_Backups_Dashboard_Pairing_Tokens::hash_secret( $secret ),
			'pairing_expires_at'  => $expires_at,
		);
	}

	/**
	 * Creates an enrollment payload.
	 *
	 * @param array<string,mixed> $overrides Overrides.
	 * @return array<string,mixed>
	 */
	private function payload( array $overrides = array() ) {
		return array_merge(
			array(
				'protocol_version'      => 1,
				'enrollment_id'        => '00000000-0000-4000-8000-000000000000',
				'site_uuid'            => '11111111-1111-4111-8111-111111111111',
				'home_url'             => 'https://client.example.com',
				'status_endpoint'      => 'https://client.example.com/wp-json/alynt-drime-backups-uploader/v1/status',
				'uploader_version'     => '0.5.3',
				'status_schema_version' => 1,
			),
			$overrides
		);
	}
}
