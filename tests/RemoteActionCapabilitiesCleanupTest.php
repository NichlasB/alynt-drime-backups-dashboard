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
		$result       = $capabilities->sanitize(
			array(
				'protocol_version'   => 2,
				'enabled'            => true,
				'sodium_available'   => true,
				'allowed_actions'    => array( 'scan_upload_now', 'cleanup_preview', 'cleanup_apply' ),
				'cleanup_management' => array(
					'protocol_version'        => 2,
					'capability_version'      => 1,
					'enabled'                 => true,
					'preview_supported'       => true,
					'apply_supported'         => false,
					'scope'                   => 'safe_local_uploader_owned',
					'supported_categories'    => array( 'uploader_temp_artifacts', 'server_backups' ),
					'requires_fresh_preview'  => true,
					'max_preview_age_seconds' => 900,
					'paths_exposed'           => true,
				),
				'last_action'        => array(
					'action_id'       => '44444444-4444-4444-8444-444444444444',
					'action_type'     => 'cleanup_preview',
					'state'           => 'succeeded',
					'code'            => 'cleanup_preview_ready',
					'summary'         => 'Cleanup preview is ready. Nothing was deleted.',
					'cleanup_preview' => array(
						'preview_action_id'    => '44444444-4444-4444-8444-444444444444',
						'preview_fingerprint'  => str_repeat( 'e', 64 ),
						'capability_version'   => 1,
						'scope'                => 'safe_local_uploader_owned',
						'preview_created_at'   => '2026-09-29T12:00:00+00:00',
						'expires_at'           => '2026-09-29T12:15:00+00:00',
						'total_eligible_count' => 2,
						'total_approx_bytes'   => 2048,
						'apply_supported'      => true,
						'categories'           => array(
							array(
								'category'       => 'uploader_temp_artifacts',
								'eligible_count' => 2,
								'approx_bytes'   => 2048,
								'age_band'       => 'older_than_24h',
								'reason_code'    => 'safe_local_uploader_owned_temp_artifacts',
							),
							array(
								'category'       => 'server_backups',
								'eligible_count' => 99,
							),
						),
					),
				),
			)
		);

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
