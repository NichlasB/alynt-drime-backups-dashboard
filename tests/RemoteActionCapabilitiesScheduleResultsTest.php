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
		$result       = $capabilities->sanitize( $this->schedule_preview_alias_summary() );

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
		$result       = $capabilities->sanitize( $this->schedule_apply_alias_summary() );

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
