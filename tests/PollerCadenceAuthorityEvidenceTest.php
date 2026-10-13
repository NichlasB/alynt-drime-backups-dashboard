<?php
/** Poller binds optional evidence to its persisted enrollment, not remote claims. */
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/support/poller-test-harness.php';
require_once __DIR__ . '/support/poller-test-fixtures.php';

class PollerCadenceAuthorityEvidenceTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Test_Poller_Fixtures;

	/** @dataProvider persisted_keys */
	public function test_poller_retains_only_evidence_bound_to_persisted_action_key( $key, $retained ) {
		$fixture = json_decode( file_get_contents( __DIR__ . '/fixtures/cadence-authority-evidence-v1.json' ), true );
		$authority = $fixture['status']; $authority['observed_at'] = time(); $authority['expires_at'] = time() + 300;
		$vault = $this->vault(); $site = $this->site( $vault, array( 'action_key_id' => $key ) );
		$sites = new Alynt_Drime_Backups_Dashboard_Test_Poller_Site_Repository( $site );
		$snapshots = new Alynt_Drime_Backups_Dashboard_Test_Poller_Snapshot_Repository();
		$poller = $this->poller( $sites, $snapshots, $vault, $this->successful_http_client( array( 'cadence_authority' => $authority ) ) );
		$this->assertSame( 'working', $poller->check_status_now( 77 )['category'] );
		$this->assertSame( $retained, isset( $snapshots->recorded['payload']['cadence_authority'] ) );
		if ( $retained ) {
			$this->assertSame( $authority, $snapshots->recorded['payload']['cadence_authority'] );
		}
		$this->assertSame( array(), $sites->failure );
	}
	public function persisted_keys() { return array( array( 'ak_fixture', true ), array( '', false ), array( 'ak_other', false ) ); }

	public function test_invalid_optional_evidence_does_not_change_legacy_health_or_allow_actions() {
		$validator = new Alynt_Drime_Backups_Dashboard_Status_Payload_Validator(); $legacy = $this->payload(); $baseline = $validator->validate( $legacy, $legacy['site_uuid'] );
		$fixture = json_decode( file_get_contents( __DIR__ . '/fixtures/cadence-authority-evidence-v1.json' ), true );
		$authority = $fixture['status']; $authority['observed_at'] = time(); $authority['expires_at'] = time() + 300;
		$this->assertSame( $baseline, $validator->validate( array_merge( $legacy, array( 'cadence_authority' => $authority ) ), $legacy['site_uuid'] ) );
		$authority['execution_supported'] = true;
		$this->assertSame( $baseline, $validator->validate( array_merge( $legacy, array( 'cadence_authority' => $authority ) ), $legacy['site_uuid'], 'ak_fixture' ) );
		$authority['private_key'] = 'forbidden-fixture';
		$this->assertSame( 'payload_invalid', $validator->validate( array_merge( $legacy, array( 'cadence_authority' => $authority ) ), $legacy['site_uuid'], 'ak_fixture' )->get_error_code() );
	}
}
