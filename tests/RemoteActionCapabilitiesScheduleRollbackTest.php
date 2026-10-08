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
		$result       = $capabilities->sanitize( $this->schedule_rollback_preview_capability_summary() );

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
