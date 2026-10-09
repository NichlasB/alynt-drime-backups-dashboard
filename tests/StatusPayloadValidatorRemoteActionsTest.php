<?php
/**
 * Status payload validator remote-action tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/status-payload-validator-test-bootstrap.php';

/**
 * Tests client status payload remote-action capability validation.
 */
class StatusPayloadValidatorRemoteActionsTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Status_Payload_Validator_Test_Fixtures;

	/**
	 * Optional remote-action capabilities are allowlisted and sanitized.
	 *
	 * @return void
	 */
	public function test_remote_action_capabilities_are_allowlisted_and_sanitized() {
		$result = $this->validate_payload(
			array_merge(
				$this->payload(),
				array(
					'remote_actions' => array(
						'protocol_version'            => 2,
						'enabled'                     => true,
						'key_id'                      => 'ak_123',
						'allowed_actions'             => array( 'scan_upload_now', 'restore_now' ),
						'sodium_available'            => true,
						'min_interval_seconds'        => 600,
						'one_running_action_per_site' => true,
						'schedule_management'         => array(
							'protocol_version'   => 2,
							'capability_version' => 1,
							'enabled'            => true,
							'preview_only'       => true,
							'apply_supported'    => false,
							'rollback_supported' => false,
							'schedules'          => array(
								array(
									'schedule_id'              => 'alynt_scan_upload',
									'label'                    => 'Alynt scan/upload',
									'owner'                    => 'alynt_uploader',
									'manageable'               => true,
									'current_cadence'          => 'every_15_minutes',
									'current_interval_seconds' => 900,
									'current_next_run_at'      => '2026-06-25T16:45:00+00:00',
									'supported_cadences'       => array( 'every_15_minutes' ),
									'minimum_interval_seconds' => 900,
									'extra_field'              => 'ignored',
								),
							),
						),
						'extra_field'                 => 'ignored',
					),
				)
			)
		);

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'remote_actions', $result );
		$this->assertTrue( $result['remote_actions']['enabled'] );
		$this->assertSame( array( 'scan_upload_now' ), $result['remote_actions']['allowed_actions'] );
		$this->assertArrayNotHasKey( 'extra_field', $result['remote_actions'] );
		$this->assertArrayHasKey( 'schedule_management', $result['remote_actions'] );
		$this->assertTrue( $result['remote_actions']['schedule_management']['enabled'] );
		$this->assertTrue( $result['remote_actions']['schedule_management']['preview_only'] );
		$this->assertFalse( $result['remote_actions']['schedule_management']['apply_supported'] );
		$this->assertFalse( $result['remote_actions']['schedule_management']['rollback_supported'] );
		$this->assertSame( 'alynt_scan_upload', $result['remote_actions']['schedule_management']['schedules'][0]['schedule_id'] );
		$this->assertArrayNotHasKey( 'extra_field', $result['remote_actions']['schedule_management']['schedules'][0] );
	}

	/**
	 * Remote-action summaries containing secrets or path-mode fields are rejected.
	 *
	 * @return void
	 */
	public function test_remote_action_forbidden_field_is_rejected() {
		$result = $this->validate_payload(
			array_merge(
				$this->payload(),
				array(
					'remote_actions' => array(
						'protocol_version' => 2,
						'enabled'          => true,
						'last_action'      => array(
							'action_id' => '11111111-1111-4111-8111-111111111111',
							'file'      => '/private/backup.zip',
						),
					),
				)
			)
		);

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'payload_invalid', $result->get_error_code() );
	}
}
