<?php
/**
 * REST request fixtures for enrollment controller tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Provides minimal REST request test doubles.
 */
trait Alynt_Drime_Backups_Dashboard_Enrollment_REST_Request_Fixtures {
	/**
	 * Creates a minimal REST request test double.
	 *
	 * @param string $authorization Authorization header.
	 * @return object
	 */
	private function request_with_authorization( $authorization ) {
		return new class( $authorization ) {
			/**
			 * Authorization header.
			 *
			 * @var string
			 */
			private $authorization;

			/**
			 * Constructor.
			 *
			 * @param string $authorization Authorization header.
			 */
			public function __construct( $authorization ) {
				$this->authorization = (string) $authorization;
			}

			/**
			 * Gets a header.
			 *
			 * @param string $name Header name.
			 * @return string
			 */
			public function get_header( $name ) {
				return 'authorization' === strtolower( (string) $name ) ? $this->authorization : '';
			}
		};
	}
}
