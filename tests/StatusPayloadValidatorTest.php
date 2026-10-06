<?php
/**
 * Status payload validator tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/status-payload-validator-test-bootstrap.php';

/**
 * Tests client status payload validation.
 */
class StatusPayloadValidatorTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Status_Payload_Validator_Test_Fixtures;

	/**
	 * Valid schema-1 payload is allowlisted and sanitized.
	 *
	 * @return void
	 */
	public function test_valid_payload_is_allowlisted() {
		$validator = $this->status_payload_validator();
		$result    = $validator->validate(
			array_merge(
				$this->payload(),
				array(
					'unexpected_future_field' => 'ignored',
				)
			),
			'11111111-1111-4111-8111-111111111111'
		);

		$this->assertIsArray( $result );
		$this->assertSame( 1, $result['schema_version'] );
		$this->assertSame( '11111111-1111-4111-8111-111111111111', $result['site_uuid'] );
		$this->assertArrayNotHasKey( 'unexpected_future_field', $result );
		$this->assertArrayNotHasKey( 'backup_sources', $result );
	}

	/**
	 * Overlong plugin versions are bounded before fixed-width storage.
	 *
	 * @return void
	 */
	public function test_overlong_plugin_version_is_bounded() {
		$validator = $this->status_payload_validator();
		$result    = $validator->validate(
			array_merge(
				$this->payload(),
				array(
					'plugin_version' => str_repeat( '9', 100 ),
				)
			),
			'11111111-1111-4111-8111-111111111111'
		);

		$this->assertIsArray( $result );
		$this->assertSame( 64, strlen( $result['plugin_version'] ) );
	}

	/**
	 * Path-mode fields are rejected.
	 *
	 * @return void
	 */
	public function test_forbidden_path_field_is_rejected() {
		$validator = $this->status_payload_validator();
		$result    = $validator->validate(
			array_merge(
				$this->payload(),
				array(
					'server_outbox_path' => '/var/www/site/private/backups',
				)
			),
			'11111111-1111-4111-8111-111111111111'
		);

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'payload_invalid', $result->get_error_code() );
	}

	/**
	 * UUID mismatch is rejected.
	 *
	 * @return void
	 */
	public function test_site_uuid_mismatch_is_rejected() {
		$validator = $this->status_payload_validator();
		$result    = $validator->validate(
			$this->payload(),
			'22222222-2222-4222-8222-222222222222'
		);

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'site_uuid_mismatch', $result->get_error_code() );
	}

}
