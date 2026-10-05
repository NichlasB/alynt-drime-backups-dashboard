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
				'schedule_management'          => array(
					'protocol_version'   => 2,
					'capability_version' => 1,
					'enabled'            => true,
					'preview_only'       => true,
					'apply_supported'    => false,
					'rollback_supported' => false,
					'schedules'          => array(
						$this->alynt_scan_upload_schedule(
							array(
								'current_interval_seconds'       => 900,
								'current_next_run_at'            => '2026-06-25T16:45:00+00:00',
								'supported_cadences'             => array( 'every_15_minutes', 'every_15_minutes' ),
								'requires_high_friction_disable' => true,
								'rollback_supported'             => true,
								'extra_field'                    => 'ignored',
							)
						),
					),
				),
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
