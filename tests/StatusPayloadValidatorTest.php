<?php
/**
 * Status payload validator tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once dirname( __DIR__ ) . '/includes/class-status-payload-validator.php';

/**
 * Tests client status payload validation.
 */
class StatusPayloadValidatorTest extends TestCase {
	/**
	 * Valid schema-1 payload is allowlisted and sanitized.
	 *
	 * @return void
	 */
	public function test_valid_payload_is_allowlisted() {
		$validator = new Alynt_Drime_Backups_Dashboard_Status_Payload_Validator();
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
	}

	/**
	 * Path-mode fields are rejected.
	 *
	 * @return void
	 */
	public function test_forbidden_path_field_is_rejected() {
		$validator = new Alynt_Drime_Backups_Dashboard_Status_Payload_Validator();
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
		$validator = new Alynt_Drime_Backups_Dashboard_Status_Payload_Validator();
		$result    = $validator->validate(
			$this->payload(),
			'22222222-2222-4222-8222-222222222222'
		);

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'site_uuid_mismatch', $result->get_error_code() );
	}

	/**
	 * Creates a valid payload.
	 *
	 * @return array<string,mixed>
	 */
	private function payload() {
		return array(
			'schema_version'              => 1,
			'site_uuid'                   => '11111111-1111-4111-8111-111111111111',
			'plugin_version'              => '0.5.3',
			'queue_count'                 => 0,
			'uploaded_count'              => 1,
			'failed_count'                => 0,
			'active_upload'               => false,
			'auto_scan_enabled'           => true,
			'server_cron_expected'        => false,
			'server_outbox_configured'    => true,
			'server_outbox_readable'      => true,
			'wpvivid_override_configured' => false,
			'old_wpvivid_uploader_active' => false,
			'wp_cron_disabled'            => false,
			'cron_status'                 => 'ok',
			'cron_reason'                 => 'Scheduled scans are available.',
			'warning_count'               => 0,
			'warnings'                    => array(),
			'last_runner'                 => 'wp_cron',
			'last_runner_at'              => 1786305600,
			'last_scheduled_scan_at'      => 1786305600,
			'last_wp_cli_scan_at'         => 0,
		);
	}
}
