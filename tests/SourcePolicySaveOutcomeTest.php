<?php
/** Acknowledgement classification DATA, including committed-but-unconfirmed RAM outcomes. */
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/support/source-policy-memory-store.php';

class SourcePolicySaveOutcomeTest extends TestCase {
	/** @dataProvider acknowledgements */
	public function test_only_exact_model_acknowledgements_can_report_success( $operation, $affected, $post, $expected ) {
		$result = Alynt_Drime_Backups_Dashboard_Source_Policy_Save_Contract::classify_result( $operation, $affected, $post );
		if ( in_array( $expected, array( 'saved', 'unchanged' ), true ) ) $this->assertSame( array( 'status' => $expected ), $result );
		else { $this->assertInstanceOf( WP_Error::class, $result ); $this->assertSame( 'source_policy_' . $expected, $result->get_error_code() ); }
	}
	public static function acknowledgements() {
		$cases = array();
		foreach ( array( 'insert', 'update' ) as $operation ) {
			foreach ( array( array( 1, true, 'saved' ), array( 0, true, 'conflict' ), array( false, true, 'storage_error' ), array( 'unavailable', true, 'unavailable' ), array( 'unknown', true, 'write_uncertain' ), array( 1, false, 'write_uncertain' ), array( 1, 'true', 'write_uncertain' ), array( 2, true, 'write_uncertain' ), array( -1, true, 'write_uncertain' ), array( true, true, 'write_uncertain' ), array( '1', true, 'write_uncertain' ), array( null, true, 'write_uncertain' ) ) as $case ) $cases[] = array_merge( array( $operation ), $case );
		}
		return array_merge( $cases, array( array( 'noop', 0, true, 'unchanged' ), array( 'noop', 1, true, 'write_uncertain' ), array( 'noop', 0, false, 'write_uncertain' ), array( 'unknown', 1, true, 'invalid' ) ) );
	}

	/** @dataProvider uncertain_finishes */
	public function test_uncertainty_can_follow_a_memory_commit_but_never_retries_or_reports_saved( $ack, $post ) {
		$store = new Alynt_Drime_Backups_Dashboard_Test_Policy_Memory_Store(); $old = $store->snapshot();
		$plan = $old->plan( $old->revision(), 7, 'wpvivid', 'external_optional' );
		$this->assertSame( 'source_policy_write_uncertain', $store->apply( $plan, $ack, $post )->get_error_code() );
		$this->assertSame( 1, $store->calls ); $this->assertSame( 1, $store->mutations );
		$this->assertSame( array( 7 => array( 'wpvivid' => 'external_optional' ) ), $store->map );
		$this->assertSame( 'source_policy_conflict', $store->snapshot()->plan( $old->revision(), 7, 'wpvivid', 'external_optional' )->get_error_code() );
	}
	public static function uncertain_finishes() { return array( array( 'unknown', true ), array( 1, false ) ); }

	public function test_definite_failure_has_no_modeled_commit_and_does_not_retry() {
		$store = new Alynt_Drime_Backups_Dashboard_Test_Policy_Memory_Store(); $old = $store->snapshot();
		$this->assertSame( 'source_policy_storage_error', $store->apply( $old->plan( $old->revision(), 7, 'wpvivid', 'external_optional' ), false )->get_error_code() );
		$this->assertSame( 1, $store->calls ); $this->assertSame( 0, $store->mutations );
		$this->assertTrue( $old->matches( $store->snapshot() ) );
	}
}
