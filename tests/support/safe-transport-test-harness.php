<?php
/**
 * Safe transport test harness.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once dirname( __DIR__, 2 ) . '/includes/class-origin-validator.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-safe-transport.php';

/**
 * Shared Safe Transport test fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Safe_Transport_Test_Fixtures {
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
