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
require_once __DIR__ . '/enrollment-manager-site-repository-double.php';

/**
 * Fixture helpers for enrollment manager tests.
 */
trait Alynt_Drime_Backups_Dashboard_Enrollment_Manager_Test_Fixtures {
	/**
	 * Builds an enrollment manager for a test repository.
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Test_Site_Repository $repository Site repository double.
	 * @return Alynt_Drime_Backups_Dashboard_Enrollment_Manager
	 */
	private function manager( Alynt_Drime_Backups_Dashboard_Test_Site_Repository $repository ) {
		return new Alynt_Drime_Backups_Dashboard_Enrollment_Manager(
			$repository,
			new Alynt_Drime_Backups_Dashboard_Origin_Validator()
		);
	}

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
