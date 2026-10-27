<?php
/** Observation identity, framing and primitive-only DATA qualification. */
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/support/source-policy-memory-store.php';

class SourcePolicySaveObservationTest extends TestCase {
	public function test_exact_bytes_presence_owner_and_autoload_are_independent_bindings() {
		$store = new Alynt_Drime_Backups_Dashboard_Test_Policy_Memory_Store();
		$initial = $store->snapshot();
		$this->assertTrue( $initial->matches( $store->snapshot() ) );
		$this->assertMatchesRegularExpression( '/\A[a-f0-9]{64}\z/', $initial->revision() );
		$store->owner = 'fixture-B'; $this->assertFalse( $initial->matches( $store->snapshot() ) );
		$this->assertNotSame( $initial->revision(), $store->snapshot()->revision() );
		$store->owner = 'fixture-A'; $store->autoload = 'off';
		$this->assertNotSame( $initial->revision(), $store->snapshot()->revision() );
		$absent = ( new Alynt_Drime_Backups_Dashboard_Test_Policy_Memory_Store( array(), false ) )->snapshot();
		$this->assertNotSame( $initial->revision(), $absent->revision() );
		$this->assertFalse( $initial->matches( array() ) );
	}

	public function test_equal_effective_views_with_different_raw_bytes_do_not_match() {
		$store = new Alynt_Drime_Backups_Dashboard_Test_Policy_Memory_Store( array( 7 => array( 'wpvivid' => 'EXTERNAL_OPTIONAL' ) ) );
		$first = $store->snapshot(); $store->map[7]['wpvivid'] = 'external_optional';
		$this->assertFalse( $first->matches( $store->snapshot() ) );
		$this->assertNotSame( $first->revision(), $store->snapshot()->revision() );
	}

	public function test_observation_freezes_primitive_input_and_unambiguously_frames_field_boundaries() {
		$map = array( 7 => array( 'wpvivid' => 'external_optional' ) );
		$old = Alynt_Drime_Backups_Dashboard_Source_Policy_Save_Contract::observe( 'fixture', true, serialize( $map ), 'no', $map );
		$map[7]['wpvivid'] = 'required';
		$this->assertSame( 'update', $old->plan( $old->revision(), 7, 'wpvivid', 'required' )['operation'] );
		$first = Alynt_Drime_Backups_Dashboard_Source_Policy_Save_Contract::observe( 'fixture-a', true, 'a:0:{}', 'bc', array() );
		$other = Alynt_Drime_Backups_Dashboard_Source_Policy_Save_Contract::observe( 'fixture-ab', true, 'a:0:{}', 'c', array() );
		$this->assertNotSame( $first->revision(), $other->revision() );
	}

	/** @dataProvider malformed_observations */
	public function test_invalid_observations_do_not_manufacture_an_empty_writable_map( $owner, $exists, $bytes, $autoload, $map ) {
		$result = Alynt_Drime_Backups_Dashboard_Source_Policy_Save_Contract::observe( $owner, $exists, $bytes, $autoload, $map );
		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'source_policy_invalid', $result->get_error_code() );
	}

	public static function malformed_observations() {
		return array(
			array( '', true, 'a:0:{}', 'no', array() ), array( "fixture\0A", true, 'a:0:{}', 'no', array() ),
			array( 'fixture-A', 1, 'a:0:{}', 'no', array() ), array( 'fixture-A', true, 'a:1:{}', 'no', array() ),
			array( 'fixture-A', true, 'O:8:"stdClass":0:{}', 'no', array() ), array( 'fixture-A', false, 'a:0:{}', '', array() ),
			array( 'fixture-A', false, '', 'no', array() ), array( 'fixture-A', true, 'a:0:{}', 'no', null ),
			array( 'fixture-A', true, 'a:0:{}', 'no', array( 7 => new Alynt_Drime_Backups_Dashboard_Test_Policy_Value_Tripwire() ) ),
			array( 'fixture-A', true, 'a:0:{}', 'no', array( 7 => array( 'wpvivid' => new Alynt_Drime_Backups_Dashboard_Test_Policy_Value_Tripwire() ) ) ),
		);
	}

	public function test_reference_graphs_and_resource_values_are_rejected_before_serialization() {
		$mode = 'external_optional'; $sources = array( 'wpvivid' => &$mode );
		foreach ( array( array( 7 => $sources ), array( 7 => &$sources ) ) as $map ) {
			$this->assertInstanceOf( WP_Error::class, Alynt_Drime_Backups_Dashboard_Source_Policy_Save_Contract::observe( 'fixture-A', true, 'unused', 'no', $map ) );
		}
		$resource = fopen( 'php://memory', 'r+' );
		try { $this->assertInstanceOf( WP_Error::class, Alynt_Drime_Backups_Dashboard_Source_Policy_Save_Contract::observe( 'fixture-A', true, 'unused', 'no', array( 7 => array( 'wpvivid' => $resource ) ) ) ); }
		finally { fclose( $resource ); }
	}

	public function test_state_equivalence_does_not_pretend_to_be_a_durable_generation_or_native_observation() {
		$store = new Alynt_Drime_Backups_Dashboard_Test_Policy_Memory_Store(); $old = $store->snapshot();
		$store->map = array( 7 => array( 'wpvivid' => 'external_optional' ) ); $this->assertFalse( $old->matches( $store->snapshot() ) );
		$store->map = array(); $this->assertTrue( $old->matches( $store->snapshot() ) );
		$this->assertSame( array(), json_decode( json_encode( $old ), true ) );
		$this->assertSame( 0, $store->mutations );
	}
}
