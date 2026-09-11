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
				'schedule_management'          => array(
					'protocol_version'   => 2,
					'capability_version' => 1,
					'enabled'            => true,
					'preview_only'       => true,
					'apply_supported'    => false,
					'rollback_supported' => false,
					'schedules'          => array(
						array(
							'schedule_id'                    => 'alynt_scan_upload',
							'label'                          => 'Alynt scan/upload',
							'owner'                          => 'alynt_uploader',
							'manageable'                     => true,
							'current_cadence'                => 'every_15_minutes',
							'current_interval_seconds'       => 900,
							'current_next_run_at'            => '2026-06-25T16:45:00+00:00',
							'supported_cadences'             => array( 'every_15_minutes', 'every_15_minutes' ),
							'minimum_interval_seconds'       => 900,
							'can_disable'                    => false,
							'requires_high_friction_disable' => true,
							'rollback_supported'             => true,
							'extra_field'                    => 'ignored',
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
		$this->assertFalse( $result['schedule_management']['rollback_supported'] );
		$this->assertSame( 'alynt_scan_upload', $result['schedule_management']['schedules'][0]['schedule_id'] );
		$this->assertSame( array( 'every_15_minutes' ), $result['schedule_management']['schedules'][0]['supported_cadences'] );
		$this->assertFalse( $result['schedule_management']['schedules'][0]['rollback_supported'] );
		$this->assertArrayNotHasKey( 'extra_field', $result['schedule_management']['schedules'][0] );
	}

	/**
	 * Preview schedule capability is disabled if a client advertises mutation support early.
	 *
	 * @return void
	 */
	public function test_schedule_management_preview_does_not_enable_apply_or_rollback() {
		$capabilities = new Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities();
		$result       = $capabilities->sanitize(
			array(
				'protocol_version'     => 2,
				'enabled'              => true,
				'schedule_management'  => array(
					'protocol_version'   => 2,
					'capability_version' => 1,
					'enabled'            => true,
					'preview_only'       => false,
					'apply_supported'    => true,
					'rollback_supported' => true,
					'schedules'          => array(
						array(
							'schedule_id'              => 'alynt_scan_upload',
							'label'                    => 'Alynt scan/upload',
							'owner'                    => 'alynt_uploader',
							'current_cadence'          => 'every_15_minutes',
							'supported_cadences'       => array( 'every_15_minutes' ),
							'minimum_interval_seconds' => 900,
						),
					),
				),
			)
		);

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'schedule_management', $result );
		$this->assertFalse( $result['schedule_management']['enabled'] );
		$this->assertFalse( $result['schedule_management']['apply_supported'] );
		$this->assertFalse( $result['schedule_management']['rollback_supported'] );
		$this->assertFalse( $capabilities->supports_schedule_management_preview( $result ) );
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
