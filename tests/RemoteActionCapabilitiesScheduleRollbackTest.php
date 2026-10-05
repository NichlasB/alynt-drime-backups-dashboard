<?php
/**
 * Remote action schedule rollback capability tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/remote-action-capabilities-test-fixtures.php';

/**
 * Tests V2 schedule rollback-preview capability sanitization.
 */
class RemoteActionCapabilitiesScheduleRollbackTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Test_Fixtures;

	/**
	 * Schedule rollback preview results are sanitized as support/audit evidence only.
	 *
	 * @return void
	 */
	public function test_schedule_rollback_preview_is_sanitized_without_enabling_rollback() {
		$capabilities = $this->remote_action_capabilities();
		$result       = $capabilities->sanitize(
			array(
				'protocol_version'    => 2,
				'enabled'             => true,
				'sodium_available'    => true,
				'allowed_actions'     => array( 'scan_upload_now', 'schedule_preview', 'schedule_rollback_preview' ),
				'schedule_management' => array(
					'protocol_version'           => 2,
					'capability_version'         => 1,
					'enabled'                    => true,
					'preview_only'               => true,
					'apply_supported'            => false,
					'rollback_preview_supported' => true,
					'rollback_supported'         => false,
					'schedules'                  => array(
						$this->alynt_scan_upload_schedule(
							array(
								'current_cadence'            => 'every_30_minutes',
								'supported_cadences'         => array( 'every_15_minutes', 'every_30_minutes' ),
								'rollback_preview_supported' => true,
								'rollback_supported'         => true,
							)
						),
					),
				),
				'last_action'         => array(
					'action_id'                 => '33333333-3333-4333-8333-333333333333',
					'action_type'               => 'schedule_rollback_preview',
					'state'                     => 'succeeded',
					'code'                      => 'schedule_rollback_preview_ready',
					'summary'                   => 'Schedule rollback preview is ready. No schedule was changed.',
					'schedule_rollback_preview' => array(
						'schedule_id'                           => 'alynt_scan_upload',
						'capability_version'                    => 1,
						'preview_action_id'                     => '33333333-3333-4333-8333-333333333333',
						'preview_fingerprint'                   => str_repeat( 'a', 64 ),
						'source_apply_action_id'                => '22222222-2222-4222-8222-222222222222',
						'rollback_metadata_fingerprint'         => str_repeat( 'd', 64 ),
						'current_cadence'                       => 'every_30_minutes',
						'applied_cadence'                       => 'every_30_minutes',
						'rollback_cadence'                      => 'every_15_minutes',
						'current_next_run_at'                   => '2026-09-15T18:53:55+00:00',
						'rollback_next_run_estimate_at'         => '2026-09-15T19:08:55+00:00',
						'current_schedule_fingerprint'          => str_repeat( 'c', 64 ),
						'expected_current_schedule_fingerprint' => str_repeat( 'c', 64 ),
						'previous_schedule_fingerprint'         => str_repeat( 'b', 64 ),
						'would_change'                          => true,
						'rollback_apply_supported'              => true,
						'rollback_supported'                    => true,
					),
				),
			)
		);

		$this->assertIsArray( $result );
		$this->assertSame( array( 'scan_upload_now', 'schedule_preview', 'schedule_rollback_preview' ), $result['allowed_actions'] );
		$this->assertTrue( $result['schedule_management']['rollback_preview_supported'] );
		$this->assertFalse( $result['schedule_management']['rollback_supported'] );
		$this->assertTrue( $result['schedule_management']['schedules'][0]['rollback_preview_supported'] );
		$this->assertFalse( $result['schedule_management']['schedules'][0]['rollback_supported'] );
		$this->assertTrue( $capabilities->supports_schedule_rollback_preview_action( $result, 'alynt_scan_upload' ) );
		$this->assertSame( 'schedule_rollback_preview', $result['last_action']['action_type'] );
		$this->assertSame( 'every_15_minutes', $result['last_action']['schedule_rollback_preview']['rollback_cadence'] );
		$this->assertFalse( $result['last_action']['schedule_rollback_preview']['rollback_apply_supported'] );
		$this->assertFalse( $result['last_action']['schedule_rollback_preview']['rollback_supported'] );
		$this->assertSame( str_repeat( 'd', 64 ), $result['last_action']['schedule_rollback_preview']['rollback_metadata_fingerprint'] );
	}
}
