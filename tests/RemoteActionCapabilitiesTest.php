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
	 * Preview schedule summaries are restricted to the supported Alynt uploader schedule.
	 *
	 * @return void
	 */
	public function test_schedule_management_preview_ignores_unsupported_schedules() {
		$capabilities = new Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities();
		$result       = $capabilities->sanitize(
			array(
				'protocol_version'     => 2,
				'enabled'              => true,
				'schedule_management'  => array(
					'protocol_version'   => 2,
					'capability_version' => 1,
					'enabled'            => true,
					'preview_only'       => true,
					'apply_supported'    => false,
					'rollback_supported' => false,
					'schedules'          => array(
						array(
							'schedule_id'              => 'third_party_schedule',
							'label'                    => 'Third-party schedule',
							'current_cadence'          => 'daily',
							'supported_cadences'       => array( 'daily' ),
							'minimum_interval_seconds' => 86400,
						),
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
		$this->assertTrue( $capabilities->supports_schedule_management_preview( $result ) );
		$this->assertCount( 1, $result['schedule_management']['schedules'] );
		$this->assertSame( 'alynt_scan_upload', $result['schedule_management']['schedules'][0]['schedule_id'] );
	}

	/**
	 * Client latest-action code aliases are normalized for dashboard reconciliation.
	 *
	 * @return void
	 */
	public function test_last_action_code_aliases_are_normalized() {
		$capabilities = new Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities();
		$result       = $capabilities->sanitize(
			array(
				'protocol_version' => 2,
				'enabled'          => true,
				'last_action'      => array(
					'action_id'        => '11111111-1111-4111-8111-111111111111',
					'action_type'      => 'schedule_preview',
					'state'            => 'succeeded',
					'code'             => 'schedule_preview_ready',
					'summary'          => 'Schedule preview is ready. No schedule was changed.',
					'schedule_preview' => array(
						'schedule_id'      => 'alynt_scan_upload',
						'current_cadence'  => 'every_15_minutes',
						'proposed_cadence' => 'every_30_minutes',
						'would_change'     => true,
					),
				),
			)
		);

		$this->assertIsArray( $result );
		$this->assertSame( 'schedule_preview_ready', $result['last_action']['result_code'] );
		$this->assertSame( 'Schedule preview is ready. No schedule was changed.', $result['last_action']['result_summary'] );
		$this->assertSame( 'every_30_minutes', $result['last_action']['schedule_preview']['proposed_cadence'] );
	}

	/**
	 * Schedule apply aliases are normalized for dashboard support output.
	 *
	 * @return void
	 */
	public function test_schedule_apply_next_run_alias_is_normalized() {
		$capabilities = new Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities();
		$result       = $capabilities->sanitize(
			array(
				'protocol_version' => 2,
				'enabled'          => true,
				'last_action'      => array(
					'action_id'      => '11111111-1111-4111-8111-111111111111',
					'action_type'    => 'schedule_apply',
					'state'          => 'succeeded',
					'code'           => 'schedule_apply_succeeded',
					'summary'        => 'Schedule apply completed for Alynt scan/upload.',
					'schedule_apply' => array(
						'schedule_id'          => 'alynt_scan_upload',
						'capability_version'   => 1,
						'preview_action_id'    => '22222222-2222-4222-8222-222222222222',
						'preview_fingerprint'  => str_repeat( 'a', 64 ),
						'previous_cadence'     => 'every_15_minutes',
						'applied_cadence'      => 'every_30_minutes',
						'previous_next_run_at' => '2026-09-15T18:30:03+00:00',
						'applied_next_run_at'  => '2026-09-15T18:53:55+00:00',
						'changed'              => true,
						'rollback_available'   => true,
						'rollback_metadata'    => array(
							'captured'                            => true,
							'available'                           => true,
							'reason'                              => 'schedule_rollback_runtime_not_implemented',
							'source_action_id'                    => '11111111-1111-4111-8111-111111111111',
							'source_preview_action_id'            => '22222222-2222-4222-8222-222222222222',
							'schedule_id'                         => 'alynt_scan_upload',
							'owner'                               => 'alynt_uploader',
							'previous_cadence'                    => 'every_15_minutes',
							'applied_cadence'                     => 'every_30_minutes',
							'previous_next_run_at'                => '2026-09-15T18:30:03+00:00',
							'applied_next_run_at'                 => '2026-09-15T18:53:55+00:00',
							'current_schedule_fingerprint_before' => str_repeat( 'b', 64 ),
							'current_schedule_fingerprint_after'  => str_repeat( 'c', 64 ),
							'captured_at'                         => '2026-09-15T18:24:12+00:00',
							'expires_at'                          => '2026-09-15T19:24:12+00:00',
						),
					),
				),
			)
		);

		$this->assertIsArray( $result );
		$this->assertSame( 'schedule_apply_succeeded', $result['last_action']['result_code'] );
		$this->assertSame( '2026-09-15T18:53:55+00:00', $result['last_action']['schedule_apply']['new_next_run_at'] );
		$this->assertSame( '22222222-2222-4222-8222-222222222222', $result['last_action']['schedule_apply']['preview_action_id'] );
		$this->assertTrue( $result['last_action']['schedule_apply']['changed'] );
		$this->assertFalse( $result['last_action']['schedule_apply']['rollback_available'] );
		$this->assertTrue( $result['last_action']['schedule_apply']['rollback_metadata']['captured'] );
		$this->assertFalse( $result['last_action']['schedule_apply']['rollback_metadata']['available'] );
		$this->assertSame( 'schedule_rollback_runtime_not_implemented', $result['last_action']['schedule_apply']['rollback_metadata']['reason'] );
		$this->assertSame( str_repeat( 'b', 64 ), $result['last_action']['schedule_apply']['rollback_metadata']['current_schedule_fingerprint_before'] );
		$this->assertSame( str_repeat( 'c', 64 ), $result['last_action']['schedule_apply']['rollback_metadata']['current_schedule_fingerprint_after'] );
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
	 * Apply-capable summaries are accepted only for guarded Alynt scan/upload cadence choices.
	 *
	 * @return void
	 */
	public function test_schedule_apply_support_requires_action_and_client_policy() {
		$capabilities = new Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities();
		$result       = $capabilities->sanitize(
			array(
				'protocol_version'     => 2,
				'enabled'              => true,
				'sodium_available'     => true,
				'allowed_actions'      => array( 'scan_upload_now', 'schedule_preview', 'schedule_apply' ),
				'schedule_management'  => array(
					'protocol_version'   => 2,
					'capability_version' => 1,
					'enabled'            => true,
					'preview_only'       => false,
					'apply_supported'    => true,
					'rollback_supported' => false,
					'schedules'          => array(
						array(
							'schedule_id'              => 'alynt_scan_upload',
							'label'                    => 'Alynt scan/upload',
							'owner'                    => 'alynt_uploader',
							'manageable'               => true,
							'current_cadence'          => 'every_15_minutes',
							'supported_cadences'       => array( 'every_15_minutes', 'every_30_minutes', 'hourly' ),
							'minimum_interval_seconds' => 900,
							'can_disable'              => false,
						),
					),
				),
				'last_action'          => array(
					'action_id'        => '11111111-1111-4111-8111-111111111111',
					'action_type'      => 'schedule_preview',
					'state'            => 'succeeded',
					'result_code'      => 'schedule_preview_ready',
					'result_summary'   => 'Schedule preview is ready.',
					'schedule_preview' => array(
						'schedule_id'                  => 'alynt_scan_upload',
						'current_cadence'              => 'every_15_minutes',
						'proposed_cadence'             => 'every_30_minutes',
						'would_change'                 => true,
						'apply_supported'              => true,
						'preview_action_id'            => '11111111-1111-4111-8111-111111111111',
						'preview_fingerprint'          => str_repeat( 'a', 64 ),
						'current_schedule_fingerprint' => str_repeat( 'b', 64 ),
						'capability_version'           => 1,
						'preview_expires_at'           => '2099-01-01T00:15:00+00:00',
					),
				),
			)
		);

		$this->assertIsArray( $result );
		$this->assertTrue( $capabilities->supports_schedule_management_preview( $result ) );
		$this->assertTrue( $capabilities->supports_schedule_preview_action( $result, 'alynt_scan_upload', 'every_30_minutes' ) );
		$this->assertTrue( $capabilities->supports_schedule_apply_action( $result, 'alynt_scan_upload', 'every_30_minutes' ) );
		$this->assertFalse( $result['schedule_management']['preview_only'] );
		$this->assertTrue( $result['schedule_management']['apply_supported'] );
		$this->assertSame( str_repeat( 'a', 64 ), $result['last_action']['schedule_preview']['preview_fingerprint'] );
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
