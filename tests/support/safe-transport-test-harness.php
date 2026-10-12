<?php
/**
 * Safe transport test harness.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once dirname( __DIR__, 2 ) . '/includes/class-origin-validator.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-safe-transport.php';

if ( ! function_exists( 'home_url' ) ) {
	/**
	 * Test home_url shim for same-origin transport checks.
	 *
	 * @param string $path   Path.
	 * @param string $scheme Scheme.
	 * @return string
	 */
	function home_url( $path = '', $scheme = null ) {
		return 'https://control.sitesmanage.com' . $path;
	}
}

/**
 * Shared Safe Transport test fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Safe_Transport_Test_Fixtures {
	/**
	 * Creates a status-fetch site fixture.
	 *
	 * @param string $origin Expected origin.
	 * @return array<string,string>
	 */
	private function status_site( $origin = 'https://client.example.com' ) {
		return array(
			'expected_origin' => $origin,
		);
	}

	/**
	 * Creates a deterministic polling authorization header fixture.
	 *
	 * @return string
	 */
	private function polling_authorization() {
		return 'Bearer adb-poll-v1.pk_example_0000000000000000.' . str_repeat( 'A', 43 );
	}

	/**
	 * Creates an HTTP response fixture.
	 *
	 * @param int    $status HTTP status.
	 * @param string $body Response body.
	 * @return array<string,mixed>
	 */
	private function http_response( $status = 200, $body = '{"schema_version":1}' ) {
		return array(
			'response' => array(
				'code' => $status,
			),
			'body'     => $body,
		);
	}

	/**
	 * Creates a transport with a public resolver by default.
	 *
	 * @param callable|null $resolver Resolver.
	 * @return Alynt_Drime_Backups_Dashboard_Safe_Transport
	 */
	private function transport( $resolver = null ) {
		if ( null === $resolver ) {
			$resolver = function () {
				return array( '93.184.216.34' );
			};
		}

		return new Alynt_Drime_Backups_Dashboard_Safe_Transport( new Alynt_Drime_Backups_Dashboard_Origin_Validator(), $resolver );
	}
}
