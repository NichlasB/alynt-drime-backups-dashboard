<?php
/** Actual validator shape rejection, conversion tripwires and guard ordering. */
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/support/status-payload-validator-test-bootstrap.php';
require_once __DIR__ . '/support/payload-scalar-shape-cases.php';
require_once __DIR__ . '/support/payload-shape-tripwires.php';

class StatusPayloadScalarShapeTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Status_Payload_Validator_Test_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Test_Payload_Scalar_Cases;

	/** @dataProvider invalid_shapes */
	public function test_compound_shapes_reject_before_conversion_or_capability_delegation( $field, $shape, $code ) {
		$value = 'object' === $shape ? new stdClass() : ( 'stringable' === $shape ? new Alynt_Drime_Backups_Dashboard_Test_Payload_Stringable() : json_decode( $shape, true ) );
		$this->assert_rejection( array_replace( $this->payload(), array( $field => $value ) ), $code );
	}

	public function test_memory_resource_is_not_an_accepted_scalar() {
		$resource = fopen( 'php://memory', 'r+' );
		try {
			foreach ( self::scalar_groups() as $group => $fields ) {
				$code = 'schema' === $group ? 'schema_unsupported' : ( 'uuid' === $group ? 'site_uuid_mismatch' : 'payload_invalid' );
				foreach ( $fields as $field ) $this->assert_rejection( array_replace( $this->payload(), array( $field => $resource ) ), $code );
			}
		} finally { fclose( $resource ); }
	}

	public function test_forbidden_key_precedes_malformed_schema_and_uuid() {
		$result = $this->validate_payload( array_replace( $this->payload(), array( 'schema_version' => array( 1 ), 'site_uuid' => new Alynt_Drime_Backups_Dashboard_Test_Payload_Stringable(), 'future' => array( 'path' => 'owned-fixture' ) ) ) );
		$this->assertSame( 'payload_invalid', $result->get_error_code() );
		$this->assertSame( 'The client status payload contains a forbidden field.', $result->get_error_message() );
	}

	public function test_unsupported_schema_precedes_uuid_and_later_field_shapes() {
		$this->assert_rejection( array_replace( $this->payload(), array( 'schema_version' => 2, 'site_uuid' => new Alynt_Drime_Backups_Dashboard_Test_Payload_Stringable(), 'queue_count' => array( 1 ) ) ), 'schema_unsupported' );
	}

	public function test_uuid_mismatch_precedes_later_field_shapes() {
		$this->assert_rejection( array_replace( $this->payload(), array( 'site_uuid' => '22222222-2222-4222-8222-222222222222', 'plugin_version' => new Alynt_Drime_Backups_Dashboard_Test_Payload_Stringable() ) ), 'site_uuid_mismatch' );
	}

	private function assert_rejection( array $payload, $code ) {
		$validator = new Alynt_Drime_Backups_Dashboard_Status_Payload_Validator( new Alynt_Drime_Backups_Dashboard_Test_Payload_Capabilities_Tripwire() );
		$result = $validator->validate( $payload, '11111111-1111-4111-8111-111111111111' );
		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( $code, $result->get_error_code() );
		$this->assertStringNotContainsString( 'owned-fixture', $result->get_error_message() );
	}
}
