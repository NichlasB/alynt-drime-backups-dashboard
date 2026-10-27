<?php
/** Actual guarded handler must not turn an error or truthy value into success. */
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/support/admin-page-actions-test-harness.php';

class SourcePolicyHandlerResultTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Admin_Action_Test_Case;

	private function handle( $value ) {
		$this->set_valid_nonce( 'alynt_drime_backups_dashboard_update_source_policy' );
		$_POST = array( 'alynt_drime_backups_dashboard_action' => 'update_source_policy', '_wpnonce' => 'valid', 'dashboard_site_id' => '42', 'source_key' => 'wpvivid', 'source_mode' => 'required' );
		$harness = $this->admin_action_harness();
		$harness->source_policy = new class( $value ) {
			public $calls = array(); private $value;
			public function __construct( $value ) { $this->value = $value; }
			public function set_source_mode( $site, $source, $mode ) { $this->calls[] = array( $site, $source, $mode ); return $this->value; }
		};
		return array( $harness->handle_for_test(), $harness );
	}

	/** @dataProvider error_codes */
	public function test_errors_never_become_success_or_reflect_storage_material( $code, $expected ) {
		list( $result, $harness ) = $this->handle( new WP_Error( $code, 'owned-private-map', array( 'raw' => 'owned-private-map' ) ) );
		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( $expected, $result->get_error_code() );
		$this->assertStringNotContainsString( 'owned-private-map', serialize( $result ) );
		$this->assertSame( 'failed', $harness->event_log->audit_calls[0]['outcome'] );
		$this->assertStringNotContainsString( 'owned-private-map', serialize( $harness->event_log->audit_calls ) );
		$this->assertSame( array( array( 42, 'wpvivid', 'required' ) ), $harness->source_policy->calls );
		if ( 'source_policy_conflict' === $code ) $this->assertStringContainsString( 'not saved', $result->get_error_message() );
		if ( 'source_policy_write_uncertain' === $code ) {
			$this->assertStringContainsString( 'may have been saved', $result->get_error_message() );
			$this->assertStringNotContainsString( 'not saved', $result->get_error_message() );
		}
	}

	public static function error_codes() {
		$cases = array();
		foreach ( array( 'conflict', 'unavailable', 'invalid', 'storage_error', 'write_uncertain' ) as $reason ) $cases[] = array( 'source_policy_' . $reason, 'source_policy_' . $reason );
		$cases[] = array( 'unexpected_owned_error', 'source_policy_save_failed' );
		return $cases;
	}

	/** @dataProvider invalid_results */
	public function test_unknown_results_are_not_boolean_success( $value ) {
		list( $result, $harness ) = $this->handle( $value );
		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'source_policy_save_failed', $result->get_error_code() );
		$this->assertSame( 'failed', $harness->event_log->audit_calls[0]['outcome'] );
	}

	public static function invalid_results() {
		return array( array( 1 ), array( '1' ), array( 'false' ), array( array( 'status' => 'saved' ) ), array( new stdClass() ), array( null ) );
	}

	/** @dataProvider boolean_results */
	public function test_legacy_literal_booleans_preserve_the_original_result( $value, $outcome ) {
		list( $result, $harness ) = $this->handle( $value );
		$this->assertSame( array( 'action' => 'update_source_policy', 'success' => $value, 'source_key' => 'wpvivid', 'mode' => 'required' ), $result );
		$this->assertSame( $outcome, $harness->event_log->audit_calls[0]['outcome'] );
	}

	public static function boolean_results() { return array( array( true, 'succeeded' ), array( false, 'failed' ) ); }
}
