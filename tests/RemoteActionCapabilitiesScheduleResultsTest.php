<?php
/**
 * Remote action schedule result capability tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/remote-action-capabilities-test-fixtures.php';

/**
 * Tests V2 schedule-result capability sanitization.
 */
class RemoteActionCapabilitiesScheduleResultsTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Test_Fixtures;

	/**
	 * Client latest-action code aliases are normalized for dashboard reconciliation.
	 *
	 * @return void
	 */
	public function test_last_action_code_aliases_are_normalized() {
		$capabilities = $this->remote_action_capabilities();
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
		$capabilities = $this->remote_action_capabilities();
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
}
