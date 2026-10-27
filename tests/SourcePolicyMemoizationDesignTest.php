<?php
/**
 * Pure PHP cache-design counterexamples against the existing sanitizer override.
 * No production option reads/writes, real WordPress hooks or cache implementation.
 */
use PHPUnit\Framework\TestCase;

class SourcePolicyMemoizationDesignTest extends TestCase {
	private function normalize( array $raw ) {
		return ( new Alynt_Drime_Backups_Dashboard_Source_Policy( $raw ) )->all_policies();
	}

	public function test_referenced_mode_mutates_both_raw_maps_but_not_cached_sanitized_output() {
		$mode = 'external_optional';
		$raw = array( 7 => array( 'wpvivid' => &$mode ) );
		$cached_raw = $raw;
		$cached_clean = $this->normalize( $raw );
		$mode = 'required';
		$this->assertSame( $raw, $cached_raw );
		$this->assertNotNull( ReflectionReference::fromArrayElement( $raw[7], 'wpvivid' ) );
		$this->assertSame( array( 7 => array( 'wpvivid' => 'external_optional' ) ), $cached_clean );
		$this->assertSame( array(), $this->normalize( $raw ) );
		$this->assertNotSame( $cached_clean, $this->normalize( $raw ) );
	}

	public function test_referenced_site_map_also_defeats_raw_array_identity_as_an_immutable_snapshot() {
		$modes = array( 'wpvivid' => 'external_optional' );
		$raw = array( 7 => &$modes );
		$cached_raw = $raw;
		$cached_clean = $this->normalize( $raw );
		$modes = array();
		$this->assertSame( $raw, $cached_raw );
		$this->assertNotNull( ReflectionReference::fromArrayElement( $raw, 7 ) );
		$this->assertSame( 'external_optional', $cached_clean[7]['wpvivid'] );
		$this->assertSame( array(), $this->normalize( $raw ) );
	}

	public function test_mutable_stringable_mode_keeps_object_identity_while_normalization_changes() {
		$mode = new class() {
			public $value = 'external_optional';
			public function __toString() { return $this->value; }
		};
		$raw = array( 7 => array( 'wpvivid' => $mode ) );
		$cached_raw = $raw;
		$cached_clean = $this->normalize( $raw );
		$mode->value = 'required';
		$this->assertSame( $raw, $cached_raw );
		$this->assertSame( 'external_optional', $cached_clean[7]['wpvivid'] );
		$this->assertSame( array(), $this->normalize( $raw ) );
	}

	public function test_plain_value_copy_is_a_distinct_control_when_input_changes() {
		$raw = array( 7 => array( 'wpvivid' => 'external_optional' ) );
		$cached_raw = $raw;
		$cached_clean = $this->normalize( $raw );
		$this->assertNull( ReflectionReference::fromArrayElement( $raw, 7 ) );
		$this->assertNull( ReflectionReference::fromArrayElement( $raw[7], 'wpvivid' ) );
		$this->assertSame( $cached_clean, $this->normalize( $cached_raw ) );
		$raw[7]['wpvivid'] = 'required';
		$this->assertNotSame( $raw, $cached_raw );
		$this->assertSame( 'external_optional', $this->normalize( $cached_raw )[7]['wpvivid'] );
		$this->assertSame( array(), $this->normalize( $raw ) );
	}

	public function test_direct_site_lookup_would_lose_existing_alias_merge_semantics() {
		$raw = array( 7 => array( 'wpvivid' => 'required' ), -7 => array( 'WPVIVID' => 'EXTERNAL_OPTIONAL' ) );
		$policy = new Alynt_Drime_Backups_Dashboard_Source_Policy( $raw );
		$this->assertSame( 'required', $raw[7]['wpvivid'] );
		$this->assertSame( 'external_optional', $policy->source_mode( array( 'id' => 7 ), 'wpvivid' ) );
		$this->assertSame( 'required', $policy->source_mode( array( 'id' => 7 ), 'unknown' ) );
	}
}
