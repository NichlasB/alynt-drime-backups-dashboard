<?php
/**
 * Enrollment REST controller site and payload fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared enrollment REST site and payload fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Enrollment_REST_Site_Payload_Fixtures {
	/**
	 * Creates a deterministic pairing secret.
	 *
	 * @param string $material Repeated secret material.
	 * @return string
	 */
	private function pairing_secret( $material = 'A' ) {
		return str_repeat( $material, 43 );
	}

	/**
	 * Creates a deterministic bearer authorization header.
	 *
	 * @param string $material Repeated secret material.
	 * @return string
	 */
	private function bearer_secret( $material = 'A' ) {
		return 'Bearer ' . $this->pairing_secret( $material );
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
