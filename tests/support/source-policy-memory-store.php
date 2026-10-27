<?php
/** Explicit RAM-only conditional store, never selected through WordPress availability. */
class Alynt_Drime_Backups_Dashboard_Test_Policy_Memory_Store {
	public $map; public $exists; public $autoload; public $owner = 'fixture-A';
	public $calls = 0; public $mutations = 0;
	public function __construct( array $map = array(), $exists = true ) {
		$this->map = $map; $this->exists = $exists; $this->autoload = $exists ? 'no' : '';
	}
	public function snapshot() {
		return Alynt_Drime_Backups_Dashboard_Source_Policy_Save_Contract::observe( $this->owner, $this->exists, $this->exists ? serialize( $this->map ) : '', $this->autoload, $this->map );
	}
	public function apply( array $plan, $ack = 1, $post = true ) {
		++$this->calls;
		if ( ! $plan['expected']->matches( $this->snapshot() ) ) return new WP_Error( 'source_policy_conflict', 'RAM conflict.' );
		if ( 'noop' === $plan['operation'] ) return Alynt_Drime_Backups_Dashboard_Source_Policy_Save_Contract::classify_result( 'noop', 0, $post );
		if ( 1 === $ack || 'unknown' === $ack ) {
			$this->map = $plan['policies']; $this->exists = true; $this->autoload = 'no'; ++$this->mutations;
		}
		return Alynt_Drime_Backups_Dashboard_Source_Policy_Save_Contract::classify_result( $plan['operation'], $ack, $post );
	}
}

/** Reject before serialization or normalization can invoke user-controlled methods. */
class Alynt_Drime_Backups_Dashboard_Test_Policy_Value_Tripwire {
	public function __serialize() { PHPUnit\Framework\Assert::fail( 'Object must not be serialized.' ); }
	public function __toString() { PHPUnit\Framework\Assert::fail( 'Object must not be converted.' ); return ''; }
}
