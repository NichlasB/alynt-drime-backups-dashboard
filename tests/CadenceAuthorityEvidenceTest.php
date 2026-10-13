<?php
/** Strict optional model evidence and inspection contracts, never execution. */
use PHPUnit\Framework\TestCase;

class CadenceAuthorityEvidenceTest extends TestCase {
	private $fixture;
	private $consumer;
	private $now = 1800000000;
	protected function setUp(): void {
		$this->fixture = json_decode( file_get_contents( __DIR__ . '/fixtures/cadence-authority-evidence-v1.json' ), true );
		$this->consumer = new Alynt_Drime_Backups_Dashboard_Cadence_Authority_Evidence( function () { return $this->now; } );
	}
	private function status( $value = null, $key = 'ak_fixture' ) {
		return $this->consumer->status( null === $value ? $this->fixture['status'] : $value, $this->fixture['status']['site_uuid'], $key );
	}
	private function preview( $value = null, $authority = null ) {
		$p = $this->fixture['preview'];
		return $this->consumer->preview( null === $value ? $p : $value, null === $authority ? $this->fixture['status'] : $authority, $p['site_uuid'], 'ak_fixture', $p['source_change_action_id'], $p['preview_action_id'], $p['intent_expires_at'] );
	}
	public function test_shared_producer_status_and_preview_are_accepted_without_enabling_execution() {
		$this->assertSame( $this->fixture['status'], $this->status() );
		$this->assertSame( $this->fixture['preview'], $this->preview() );
		$this->assertFalse( $this->preview()['eligible'] );
		$this->assertFalse( $this->status()['dispatch_supported'] );
		$this->assertFalse( $this->preview()['execution_supported'] );
	}
	public function test_reordered_fields_do_not_change_canonical_preview_fingerprint() {
		$this->assertSame( $this->fixture['preview'], $this->preview( array_reverse( $this->fixture['preview'], true ) ) );
	}
	/** @dataProvider invalid_status_fields */
	public function test_unknown_inconsistent_or_unsafe_status_extension_is_omitted( $field, $value ) {
		$status = $this->fixture['status']; $status[$field] = $value;
		$this->assertSame( array(), $this->status( $status ) );
	}
	public function invalid_status_fields() {
		return array(
			array( 'contract_version', 2 ), array( 'contract_version', '1' ), array( 'mode', 'legacy' ), array( 'site_uuid', '22222222-2222-4222-8222-222222222222' ),
			array( 'key_id', 'ak_self_reported' ), array( 'authority_revision', '1' ), array( 'authority_revision', -1 ), array( 'current_cadence', 'daily' ),
			array( 'next_due_at', '1800000900' ), array( 'next_due_at', 0 ), array( 'authority_fingerprint', array() ), array( 'model_enabled', 1 ), array( 'pending', 'completed' ),
			array( 'observed_at', 1800000001 ), array( 'expires_at', 1800000000 ), array( 'expires_at', 1800000301 ), array( 'dispatch_supported', true ), array( 'execution_supported', true ),
			array( 'dispatch_supported', 0 ), array( 'enrollment_binding', str_repeat( 'a', 64 ) ), array( 'claims', array( 'private' ) ),
		);
	}
	public function test_missing_fields_key_context_and_exact_expiry_fail_closed() {
		$s = $this->fixture['status']; unset( $s['pending'] ); $this->assertSame( array(), $this->status( $s ) );
		$this->assertSame( array(), $this->status( null, '' ) ); $this->assertSame( array(), $this->status( null, 'ak_rotated' ) );
		$this->now += 300; $this->assertSame( array(), $this->status() ); $this->assertSame( array(), $this->preview() );
	}
	/** @dataProvider invalid_preview_fields */
	public function test_preview_cannot_change_binding_source_revision_cadence_or_expiry( $field, $value ) {
		$p = $this->fixture['preview']; $p[$field] = $value;
		$this->assertSame( array(), $this->preview( $p ) );
	}
	public function invalid_preview_fields() {
		return array(
			array( 'kind', 'schedule_rollback_preview' ), array( 'mode', 'legacy' ), array( 'contract_version', 2 ), array( 'key_id', 'ak_other' ),
			array( 'source_change_action_id', '22222222-2222-4222-8222-222222222222' ), array( 'preview_action_id', '11111111-1111-4111-8111-000000000001' ),
			array( 'authority_revision', 2 ), array( 'authority_fingerprint', str_repeat( 'b', 64 ) ), array( 'rollback_cadence', 'every_15_minutes' ), array( 'current_cadence', 'hourly' ),
			array( 'source_created_at', 1800000001 ), array( 'source_expires_at', 1800003601 ), array( 'preview_created_at', 1800000001 ),
			array( 'preview_expires_at', 1800000301 ), array( 'intent_expires_at', 1800000301 ), array( 'preview_fingerprint', str_repeat( 'b', 64 ) ),
			array( 'safety_checks_passed', false ), array( 'eligible', true ), array( 'dispatch_supported', true ), array( 'execution_supported', true ), array( 'journal', array() ),
		);
	}
	public function test_pending_disabled_or_stale_current_authority_blocks_preview() {
		foreach ( array( 'claimed', 'started', 'recovery_required' ) as $phase ) {
			$s = $this->fixture['status']; $s['pending'] = $phase; $this->assertSame( array(), $this->preview( null, $s ) );
		}
		$s = $this->fixture['status']; $s['model_enabled'] = false; $this->assertSame( array(), $this->preview( null, $s ) );
		$s = $this->fixture['status']; $s['authority_revision']++; $this->assertSame( array(), $this->preview( null, $s ) );
		$this->assertSame( array(), $this->preview( null, array() ) );
	}
	public function test_matching_fingerprint_is_not_a_signature_or_execution_authorization() {
		$p = $this->fixture['preview']; $p['eligible'] = true;
		$hash = $p; unset( $hash['preview_fingerprint'] ); $p['preview_fingerprint'] = hash( 'sha256', wp_json_encode( $hash ) );
		$this->assertSame( array(), $this->preview( $p ) );
	}
	public function test_recomputed_hash_does_not_allow_overlong_or_stale_intent_and_source_windows() {
		foreach ( array( 'source_expires_at' => 1800003601, 'preview_created_at' => 1799999699, 'intent_expires_at' => 1800000301 ) as $field => $value ) {
			$p = $this->fixture['preview']; $p[$field] = $value;
			$hash = $p; unset( $hash['preview_fingerprint'] ); $p['preview_fingerprint'] = hash( 'sha256', wp_json_encode( $hash ) );
			$this->assertSame( array(), $this->preview( $p ) );
		}
	}
}
