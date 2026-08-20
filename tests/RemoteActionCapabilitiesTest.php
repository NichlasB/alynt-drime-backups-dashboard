<?php
/**
 * Remote action capability tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

/**
 * Tests V2 remote-action capability sanitization.
 */
class RemoteActionCapabilitiesTest extends TestCase {
	/**
	 * Valid capability summaries are allowlisted and bounded.
	 *
	 * @return void
	 */
	public function test_capabilities_are_allowlisted_and_support_detection_is_explicit() {
		$capabilities = new Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities();
		$result       = $capabilities->sanitize(
			array(
				'protocol_version'             => 2,
				'enabled'                      => true,
				'key_id'                       => 'ak_valid.123',
				'allowed_actions'              => array(
					'scan_upload_now',
					'delete_backup',
					'scan_upload_now',
				),
				'sodium_available'             => true,
				'min_interval_seconds'         => 900,
				'one_running_action_per_site'  => true,
				'last_action'                  => array(
					'action_id'      => '11111111-1111-4111-8111-111111111111',
					'action_type'    => 'scan_upload_now',
					'state'          => 'succeeded',
					'requested_at'   => '2026-08-20T12:00:00+00:00',
					'completed_at'   => '2026-08-20T12:01:00+00:00',
					'result_code'    => 'ok',
					'result_summary' => str_repeat( 'A', 200 ),
					'counts'         => array(
						'found'            => 3,
						'queued'           => 1,
						'already_known'    => 2,
						'upload_attempted' => 1,
						'failed'           => -3,
					),
					'extra_field'    => 'ignored',
				),
				'extra_field'                  => 'ignored',
			)
		);

		$this->assertIsArray( $result );
		$this->assertTrue( $capabilities->supports_scan_upload_now( $result ) );
		$this->assertSame( array( 'scan_upload_now' ), $result['allowed_actions'] );
		$this->assertSame( 'ak_valid.123', $result['key_id'] );
		$this->assertSame( 900, $result['min_interval_seconds'] );
		$this->assertArrayNotHasKey( 'extra_field', $result );
		$this->assertSame( 160, strlen( $result['last_action']['result_summary'] ) );
		$this->assertSame( 0, $result['last_action']['counts']['failed'] );
		$this->assertArrayNotHasKey( 'extra_field', $result['last_action'] );
	}

	/**
	 * Capability summaries with forbidden keys are rejected.
	 *
	 * @return void
	 */
	public function test_forbidden_capability_fields_are_rejected() {
		$capabilities = new Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities();
		$result       = $capabilities->sanitize(
			array(
				'protocol_version' => 2,
				'enabled'          => true,
				'last_action'      => array(
					'action_id' => '11111111-1111-4111-8111-111111111111',
					'token'     => 'secret-token',
				),
			)
		);

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'payload_invalid', $result->get_error_code() );
	}
}
