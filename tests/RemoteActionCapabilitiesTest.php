<?php
/**
 * Remote action capability tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/remote-action-capabilities-test-fixtures.php';

/**
 * Tests V2 remote-action capability sanitization.
 */
class RemoteActionCapabilitiesTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Test_Fixtures;

	/**
	 * Valid capability summaries are allowlisted and bounded.
	 *
	 * @return void
	 */
	public function test_capabilities_are_allowlisted_and_support_detection_is_explicit() {
		$capabilities = $this->remote_action_capabilities();
		$result       = $capabilities->sanitize( $this->valid_remote_action_capability_summary() );

		$this->assertIsArray( $result );
		$this->assertTrue( $capabilities->supports_scan_upload_now( $result ) );
		$this->assertSame( array( 'scan_upload_now' ), $result['allowed_actions'] );
		$this->assertSame( 'ak_valid.123', $result['key_id'] );
		$this->assertSame( 900, $result['min_interval_seconds'] );
		$this->assertArrayNotHasKey( 'extra_field', $result );
		$this->assertSame( 160, strlen( $result['last_action']['result_summary'] ) );
		$this->assertSame( 0, $result['last_action']['counts']['failed'] );
		$this->assertArrayNotHasKey( 'extra_field', $result['last_action'] );
		$this->assertTrue( $capabilities->supports_schedule_management_preview( $result ) );
		$this->assertSame( 1, $result['schedule_management']['capability_version'] );
		$this->assertFalse( $result['schedule_management']['apply_supported'] );
		$this->assertFalse( $result['schedule_management']['rollback_preview_supported'] );
		$this->assertFalse( $result['schedule_management']['rollback_supported'] );
		$this->assertSame( 'alynt_scan_upload', $result['schedule_management']['schedules'][0]['schedule_id'] );
		$this->assertSame( array( 'every_15_minutes' ), $result['schedule_management']['schedules'][0]['supported_cadences'] );
		$this->assertFalse( $result['schedule_management']['schedules'][0]['rollback_preview_supported'] );
		$this->assertFalse( $result['schedule_management']['schedules'][0]['rollback_supported'] );
		$this->assertArrayNotHasKey( 'extra_field', $result['schedule_management']['schedules'][0] );
	}

	/**
	 * Capability summaries with forbidden keys are rejected.
	 *
	 * @return void
	 */
	public function test_forbidden_capability_fields_are_rejected() {
		$capabilities = $this->remote_action_capabilities();
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
