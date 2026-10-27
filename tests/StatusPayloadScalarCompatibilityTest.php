<?php
/** Legacy scalar normalization and missing/null semantics remain compatible. */
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/support/status-payload-validator-test-bootstrap.php';
require_once __DIR__ . '/support/payload-scalar-shape-cases.php';

class StatusPayloadScalarCompatibilityTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Status_Payload_Validator_Test_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Test_Payload_Scalar_Cases;

	/** @dataProvider legacy_scalars */
	public function test_existing_scalar_normalization_still_accepts( $field, $value, $expected ) {
		$result = $this->validate_payload( array_replace( $this->payload(), array( $field => $value ) ) );
		$this->assertIsArray( $result );
		$this->assertSame( $expected, $result[ $field ] );
	}

	/** @dataProvider absent_scalars */
	public function test_missing_and_null_retain_existing_fallback_or_error( $field, $state, $code, $expected ) {
		$payload = $this->payload();
		if ( 'missing' === $state ) unset( $payload[ $field ] );
		else $payload[ $field ] = null;
		$result = $this->validate_payload( $payload );
		if ( '' !== $code ) {
			$this->assertInstanceOf( WP_Error::class, $result );
			$this->assertSame( $code, $result->get_error_code() );
		} else {
			$this->assertIsArray( $result );
			$this->assertSame( $expected, $result[ $field ] );
		}
	}

	public function test_baseline_projection_is_unchanged_and_unknown_structures_are_omitted() {
		$payload = $this->payload();
		$payload['future_extension'] = array( 'fixture' => array( 1, 2 ) );
		$this->assertSame( $this->payload(), $this->validate_payload( $payload ) );
	}

	public function test_all_supported_structured_extensions_survive_the_scalar_guard() {
		$fixture = json_decode( file_get_contents( __DIR__ . '/fixtures/cadence-authority-evidence-v1.json' ), true );
		$authority = $fixture['status'];
		$authority['observed_at'] = time() - 1;
		$authority['expires_at'] = $authority['observed_at'] + 300;
		$payload = array_replace( $this->payload(), array(
			'warnings' => array( array( 'code' => 'fixture_warning', 'message' => 'Fixture warning.' ) ),
			'backup_sources' => array( 'server' => array( 'configured' => true ) ),
			'restore_readiness' => array( 'candidates' => array( array( 'source' => 'server', 'candidate_ref' => 'fixture_ref' ) ) ),
			'remote_actions' => array( 'protocol_version' => 2, 'enabled' => false ),
			'cadence_authority' => $authority,
		) );
		$result = $this->status_payload_validator()->validate( $payload, $payload['site_uuid'], 'ak_fixture' );
		$this->assertIsArray( $result );
		$this->assertSame( $payload['warnings'], $result['warnings'] );
		$this->assertTrue( $result['backup_sources']['server']['configured'] );
		$this->assertSame( 'fixture_ref', $result['restore_readiness']['candidates'][0]['candidate_ref'] );
		$this->assertFalse( $result['remote_actions']['enabled'] );
		$this->assertSame( $authority, $result['cadence_authority'] );
		$this->assertFalse( $result['cadence_authority']['execution_supported'] );
	}
}
