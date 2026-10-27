<?php
/**
 * Proves handler rejection/admission without running any protected operation.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

class Alynt_Drime_Backups_Dashboard_Test_Request_ID_Tripwire {
	private $allow_entry;

	public function __construct( $allow_entry ) {
		$this->allow_entry = $allow_entry;
	}

	public function __call( $method, array $arguments ) {
		if ( ! $this->allow_entry ) {
			PHPUnit\Framework\Assert::fail( 'Protected collaborator reached: ' . $method );
		}
		// Sentinel means entry was observed, not that the protected action succeeded.
		throw new RuntimeException( 'delegate:' . $method . ':' . $arguments[0] );
	}
}
