<?php
/**
 * Enrollment manager test harness.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once dirname( __DIR__, 2 ) . '/includes/class-origin-validator.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-pairing-tokens.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-site-repository-reads.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-site-repository-local-state-writes.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-site-repository-writes.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-site-repository-runtime-writes.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-site-repository.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-enrollment-manager.php';

/**
 * Fake repository for enrollment manager tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Site_Repository extends Alynt_Drime_Backups_Dashboard_Site_Repository {
	/**
	 * Last inserted data.
	 *
	 * @var array<string,mixed>
	 */
	public $last_insert = array();

	/**
	 * Create result override.
	 *
	 * @var int|WP_Error
	 */
	public $create_result = 123;

	/**
	 * Existing active pending site.
	 *
	 * @var array<string,mixed>|null
	 */
	public $active_pending = null;

	/**
	 * Creates a pending fake row.
	 *
	 * @param array $data Site data.
	 * @return int|WP_Error
	 */
	public function create_pending( array $data ) {
		$this->last_insert = $data;

		return $this->create_result;
	}

	/**
	 * Gets an active pending fake row.
	 *
	 * @param string $expected_origin Expected origin.
	 * @param string $now Current UTC datetime.
	 * @return array<string,mixed>|null
	 */
	public function get_active_pending_by_expected_origin( $expected_origin, $now = '' ) {
		return $this->active_pending;
	}
}

/**
 * Fixture helpers for enrollment manager tests.
 */
trait Alynt_Drime_Backups_Dashboard_Enrollment_Manager_Test_Fixtures {
	/**
	 * Extracts the plaintext secret from a display token for test assertions.
	 *
	 * @param string $token Pairing token.
	 * @return string
	 */
	private function secret_from_token( $token ) {
		$encoded = substr( $token, strlen( Alynt_Drime_Backups_Dashboard_Pairing_Tokens::TOKEN_PREFIX ) );
		$padded  = $encoded . str_repeat( '=', ( 4 - strlen( $encoded ) % 4 ) % 4 );
		$json    = base64_decode( strtr( $padded, '-_', '+/' ) );
		$payload = json_decode( $json, true );

		return is_array( $payload ) && isset( $payload['secret'] ) ? (string) $payload['secret'] : '';
	}
}
