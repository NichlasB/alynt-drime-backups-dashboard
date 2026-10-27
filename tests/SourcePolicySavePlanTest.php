<?php
/** Real model plans plus a conditional RAM store, not a native write proof. */
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/support/source-policy-memory-store.php';

class SourcePolicySavePlanTest extends TestCase {
	private function plan( $store, $site, $mode ) {
		$snapshot = $store->snapshot();
		return $snapshot->plan( $snapshot->revision(), $site, 'wpvivid', $mode );
	}

	public function test_update_preserves_other_sites_and_existing_alias_normalization() {
		$store = new Alynt_Drime_Backups_Dashboard_Test_Policy_Memory_Store( array( 7 => array( 'wpvivid' => 'required' ), -7 => array( 'WPVIVID' => 'EXTERNAL_OPTIONAL' ), 8 => array( 'wpvivid' => 'external_optional' ) ) );
		$plan = $this->plan( $store, 7, 'required' );
		$this->assertSame( 'update', $plan['operation'] );
		$this->assertSame( array( 8 => array( 'wpvivid' => 'external_optional' ) ), $plan['policies'] );
		$this->assertFalse( $plan['autoload'] );
		$this->assertSame( array( 'status' => 'saved' ), $store->apply( $plan ) );
		$this->assertSame( $plan['policies'], $store->map );
		$this->assertSame( 1, $store->mutations );
	}

	/** @dataProvider row_states */
	public function test_fresh_noop_does_not_create_or_rewrite_storage( $exists ) {
		$store = new Alynt_Drime_Backups_Dashboard_Test_Policy_Memory_Store( array(), $exists );
		$plan = $this->plan( $store, 7, 'required' );
		$this->assertSame( 'noop', $plan['operation'] );
		$this->assertSame( array( 'status' => 'unchanged' ), $store->apply( $plan ) );
		$this->assertSame( $exists, $store->exists ); $this->assertSame( 0, $store->mutations );
	}
	public static function row_states() { return array( array( true ), array( false ) ); }

	/** @dataProvider conflicting_commands */
	public function test_stale_same_or_other_site_and_first_creator_conflict_without_retry( $exists, $second_site ) {
		$store = new Alynt_Drime_Backups_Dashboard_Test_Policy_Memory_Store( array(), $exists );
		$first = $this->plan( $store, 7, 'external_optional' ); $second = $this->plan( $store, $second_site, 'external_optional' );
		$this->assertSame( $exists ? 'update' : 'insert', $first['operation'] );
		$this->assertSame( array( 'status' => 'saved' ), $store->apply( $first ) );
		$rejected = $store->apply( $second ); $this->assertInstanceOf( WP_Error::class, $rejected );
		$this->assertSame( 'source_policy_conflict', $rejected->get_error_code() );
		$this->assertSame( array( 7 => array( 'wpvivid' => 'external_optional' ) ), $store->map );
		$this->assertSame( 2, $store->calls ); $this->assertSame( 1, $store->mutations );
		$this->assertSame( array( 'status' => 7 === $second_site ? 'unchanged' : 'saved' ), $store->apply( $this->plan( $store, $second_site, 'external_optional' ) ) );
	}
	public static function conflicting_commands() { return array( array( true, 7 ), array( true, 8 ), array( false, 7 ), array( false, 8 ) ); }

	public function test_stale_already_satisfied_intent_conflicts_before_noop_and_old_noop_cannot_adopt_new_state() {
		$store = new Alynt_Drime_Backups_Dashboard_Test_Policy_Memory_Store(); $old = $store->snapshot();
		$noop = $old->plan( $old->revision(), 7, 'wpvivid', 'required' );
		$store->apply( $this->plan( $store, 7, 'external_optional' ) );
		$rejected = $store->apply( $noop ); $this->assertInstanceOf( WP_Error::class, $rejected );
		$this->assertSame( 'source_policy_conflict', $rejected->get_error_code() );
		$fresh = $store->snapshot();
		$this->assertSame( 'source_policy_conflict', $fresh->plan( $old->revision(), 7, 'wpvivid', 'external_optional' )->get_error_code() );
		$this->assertSame( 1, $store->mutations );
	}

	/** @dataProvider invalid_commands */
	public function test_invalid_commands_never_supply_a_plan( $revision, $site, $source, $mode ) {
		$snapshot = ( new Alynt_Drime_Backups_Dashboard_Test_Policy_Memory_Store() )->snapshot();
		$result = $snapshot->plan( null === $revision ? $snapshot->revision() : $revision, $site, $source, $mode );
		$this->assertInstanceOf( WP_Error::class, $result ); $this->assertSame( 'source_policy_invalid', $result->get_error_code() );
	}
	public static function invalid_commands() {
		return array( array( '', 7, 'wpvivid', 'required' ), array( array(), 7, 'wpvivid', 'required' ), array( str_repeat( 'A', 64 ), 7, 'wpvivid', 'required' ), array( null, array( 7 ), 'wpvivid', 'required' ), array( null, 0, 'wpvivid', 'required' ), array( null, 7, 'unknown', 'required' ), array( null, 7, 'wpvivid', new stdClass() ) );
	}

	public function test_legacy_command_normalization_and_raw_alias_noop_do_not_write_options() {
		$store = new Alynt_Drime_Backups_Dashboard_Test_Policy_Memory_Store( array( -7 => array( 'WPVIVID' => 'EXTERNAL_OPTIONAL' ) ) );
		$snapshot = $store->snapshot(); $plan = $snapshot->plan( $snapshot->revision(), '-7', 'WPVIVID', 'EXTERNAL_OPTIONAL' );
		$this->assertSame( 'noop', $plan['operation'] );
		$store->apply( $plan ); $this->assertArrayHasKey( -7, $store->map );
		$this->assertSame( 'update', $snapshot->plan( $snapshot->revision(), 7, 'wpvivid', 'unknown-defaults-required' )['operation'] );
		$this->assertSame( 0, $store->mutations );
	}
}
