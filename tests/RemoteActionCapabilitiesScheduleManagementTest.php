<?php
/**
 * Remote action schedule management capability tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/remote-action-capabilities-test-fixtures.php';

/**
 * Tests V2 schedule-management capability sanitization.
 */
class RemoteActionCapabilitiesScheduleManagementTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Test_Fixtures;

	/**
	 * Preview schedule summaries are restricted to the supported Alynt uploader schedule.
	 *
	 * @return void
	 */
	public function test_schedule_management_preview_ignores_unsupported_schedules() {
		$capabilities = $this->remote_action_capabilities();
		$result       = $capabilities->sanitize(
			array(
				'protocol_version'    => 2,
				'enabled'             => true,
				'schedule_management' => array(
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
						$this->alynt_scan_upload_schedule(),
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
	 * Preview schedule capability is disabled if a client advertises mutation support early.
	 *
	 * @return void
	 */
	public function test_schedule_management_preview_does_not_enable_apply_or_rollback() {
		$capabilities = $this->remote_action_capabilities();
		$result       = $capabilities->sanitize(
			array(
				'protocol_version'    => 2,
				'enabled'             => true,
				'schedule_management' => array(
					'protocol_version'   => 2,
					'capability_version' => 1,
					'enabled'            => true,
					'preview_only'       => false,
					'apply_supported'    => true,
					'rollback_supported' => true,
					'schedules'          => array(
						$this->alynt_scan_upload_schedule(),
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
		$capabilities = $this->remote_action_capabilities();
		$result       = $capabilities->sanitize(
			array(
				'protocol_version'    => 2,
				'enabled'             => true,
				'sodium_available'    => true,
				'allowed_actions'     => array( 'scan_upload_now', 'schedule_preview', 'schedule_apply' ),
				'schedule_management' => array(
					'protocol_version'   => 2,
					'capability_version' => 1,
					'enabled'            => true,
					'preview_only'       => false,
					'apply_supported'    => true,
					'rollback_supported' => false,
					'schedules'          => array(
						$this->alynt_scan_upload_schedule(
							array(
								'supported_cadences' => array( 'every_15_minutes', 'every_30_minutes', 'hourly' ),
							)
						),
					),
				),
				'last_action'         => array(
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
}
