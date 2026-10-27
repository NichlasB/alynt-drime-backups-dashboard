<?php
/** Actual transport/validator/poller flow with synthetic HTTP and storage only. */
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/support/poller-test-harness.php';
require_once __DIR__ . '/support/poller-test-fixtures.php';

class PollerScalarShapeRejectionTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Test_Poller_Fixtures;

	/** @dataProvider malformed_scalars */
	public function test_malformed_scalar_cannot_become_successful_snapshot_or_reconciliation( $field, $code ) {
		$vault = $this->vault();
		$sites = new Alynt_Drime_Backups_Dashboard_Test_Poller_Site_Repository( $this->site( $vault ) );
		$snapshots = new Alynt_Drime_Backups_Dashboard_Test_Poller_Snapshot_Repository();
		$reconciler = new Alynt_Drime_Backups_Dashboard_Test_Poller_Remote_Action_Reconciler();
		$calls = 0;
		$poller = $this->poller( $sites, $snapshots, $vault, $this->successful_counting_http_client( $calls, array( $field => array( 'fixture' => 1 ) ) ), $reconciler );
		$result = $poller->check_status_now( 77 );
		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( $code, $result->get_error_code() );
		$this->assertSame( 1, $calls );
		$this->assertSame( array(), $snapshots->recorded );
		$this->assertSame( array(), $sites->success );
		$this->assertSame( array(), $reconciler->calls );
		$this->assertSame( $code, $sites->failure['error_code'] );
	}

	public static function malformed_scalars() {
		return array( array( 'schema_version', 'schema_unsupported' ), array( 'queue_count', 'payload_invalid' ), array( 'active_upload', 'payload_invalid' ), array( 'plugin_version', 'payload_invalid' ) );
	}

	public function test_valid_wire_payload_still_records_success_and_reconciles_once() {
		$vault = $this->vault();
		$sites = new Alynt_Drime_Backups_Dashboard_Test_Poller_Site_Repository( $this->site( $vault ) );
		$snapshots = new Alynt_Drime_Backups_Dashboard_Test_Poller_Snapshot_Repository();
		$reconciler = new Alynt_Drime_Backups_Dashboard_Test_Poller_Remote_Action_Reconciler();
		$calls = 0;
		$result = $this->poller( $sites, $snapshots, $vault, $this->successful_counting_http_client( $calls ), $reconciler )->check_status_now( 77 );
		$this->assertIsArray( $result );
		$this->assertSame( 1, $calls );
		$this->assertSame( $this->payload(), $snapshots->recorded['payload'] );
		$this->assertNotEmpty( $sites->success );
		$this->assertCount( 1, $reconciler->calls );
	}
}
