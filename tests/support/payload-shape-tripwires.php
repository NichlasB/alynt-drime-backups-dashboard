<?php
/** Fail on conversion/delegation rather than silently substituting safe output. */
class Alynt_Drime_Backups_Dashboard_Test_Payload_Stringable {
	public function __toString() {
		PHPUnit\Framework\Assert::fail( 'Unsupported object must not be converted.' );
		return '';
	}
}

class Alynt_Drime_Backups_Dashboard_Test_Payload_Capabilities_Tripwire extends Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities {
	public function sanitize( $payload ) {
		PHPUnit\Framework\Assert::fail( 'Malformed scalar must stop before capability delegation.' );
	}
}
