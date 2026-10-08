<?php
/**
 * Remote action cleanup capability tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/remote-action-capabilities-test-fixtures.php';

/**
 * Tests V2 cleanup-preview capability sanitization.
 */
class RemoteActionCapabilitiesCleanupTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities_Test_Fixtures;

	/**
	 * Cleanup preview capability and result summaries are sanitized as preview-only evidence.
	 *
	 * @return void
	 */
	public function test_cleanup_preview_is_sanitized_without_enabling_cleanup_apply() {
		$capabilities = $this->remote_action_capabilities();
		$result       = $capabilities->sanitize( $this->cleanup_preview_capability_summary() );

		$this->assertIsArray( $result );
		$this->assertSame( array( 'scan_upload_now', 'cleanup_preview' ), $result['allowed_actions'] );
		$this->assertTrue( $capabilities->supports_cleanup_preview_action( $result ) );
		$this->assertTrue( $result['cleanup_management']['enabled'] );
		$this->assertTrue( $result['cleanup_management']['preview_supported'] );
		$this->assertFalse( $result['cleanup_management']['apply_supported'] );
		$this->assertFalse( $result['cleanup_management']['paths_exposed'] );
		$this->assertSame( array( 'uploader_temp_artifacts' ), $result['cleanup_management']['supported_categories'] );
		$this->assertSame( 'cleanup_preview', $result['last_action']['action_type'] );
		$this->assertSame( 2, $result['last_action']['cleanup_preview']['total_eligible_count'] );
		$this->assertSame( 2048, $result['last_action']['cleanup_preview']['total_approx_bytes'] );
		$this->assertFalse( $result['last_action']['cleanup_preview']['apply_supported'] );
		$this->assertCount( 1, $result['last_action']['cleanup_preview']['categories'] );
		$this->assertSame( 'uploader_temp_artifacts', $result['last_action']['cleanup_preview']['categories'][0]['category'] );
	}
}
