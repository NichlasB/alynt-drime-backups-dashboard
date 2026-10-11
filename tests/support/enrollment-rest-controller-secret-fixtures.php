<?php
/**
 * Enrollment REST controller secret fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared enrollment REST secret fixtures.
 */
trait Alynt_Drime_Backups_Dashboard_Enrollment_REST_Secret_Fixtures {
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
}
