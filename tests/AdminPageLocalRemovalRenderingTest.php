<?php
/**
 * Admin local-removal preview rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-polling-state-rendering-bootstrap.php';

/**
 * Tests local-removal preview and compact readiness rendering.
 */
class AdminPageLocalRemovalRenderingTest extends TestCase {
	/**
	 * Archived terminal records show a read-only removal preview with dependent counts.
	 *
	 * @return void
	 */
	public function test_archived_terminal_site_detail_shows_removal_preview_counts() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$html    = $harness->retained_record_removal_preview_panel_html(
			$this->archived_terminal_site(),
			3,
			2,
			0
		);

		$this->assertStringContainsString( 'Local Removal Preview', $html );
		$this->assertStringContainsString( 'Preview only. Permanent local removal is not available in this release', $html );
		$this->assertStringContainsString( 'Eligible for future removal', $html );
		$this->assertStringContainsString( 'Retained snapshots', $html );
		$this->assertStringContainsString( '<dd>3</dd>', $html );
		$this->assertStringContainsString( 'Retained action history rows', $html );
		$this->assertStringContainsString( '<dd>2</dd>', $html );
		$this->assertStringNotContainsString( '<form', $html );
	}

	/**
	 * Archived rows can show compact local removal-readiness hints.
	 *
	 * @return void
	 */
	public function test_archived_row_hint_shows_local_removal_readiness_counts() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$html    = $harness->retained_record_removal_row_hint_html(
			$this->archived_terminal_site(),
			3,
			2,
			0
		);

		$this->assertStringContainsString( 'Local removal: ready for future gate', $html );
		$this->assertStringContainsString( 'Snapshots 3 · actions 2 · non-terminal 0', $html );
		$this->assertStringNotContainsString( '<form', $html );
	}

	/**
	 * Unarchived records do not show the future removal preview.
	 *
	 * @return void
	 */
	public function test_unarchived_site_detail_does_not_show_removal_preview() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$html    = $harness->retained_record_removal_preview_panel_html(
			array(
				'id'                => 7,
				'enrollment_status' => 'revoked',
				'archived_at'       => '',
			)
		);

		$this->assertSame( '', $html );
	}

	/**
	 * Archived records with non-terminal action history are not eligible yet.
	 *
	 * @return void
	 */
	public function test_archived_site_detail_removal_preview_blocks_non_terminal_actions() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$html    = $harness->retained_record_removal_preview_panel_html(
			$this->archived_terminal_site(),
			0,
			4,
			1
		);

		$this->assertStringContainsString( 'Not eligible yet', $html );
		$this->assertStringContainsString( 'One or more retained action rows are still non-terminal', $html );
		$this->assertStringContainsString( 'Non-terminal action rows', $html );
		$this->assertStringContainsString( '<dd>1</dd>', $html );
		$this->assertStringNotContainsString( '<form', $html );
	}

	/**
	 * Returns an archived terminal local record fixture.
	 *
	 * @return array<string,mixed>
	 */
	private function archived_terminal_site() {
		return array(
			'id'                            => 7,
			'enrollment_status'             => 'revoked',
			'archived_at'                   => '2026-09-19 18:30:00',
			'polling_key_id'                => '',
			'polling_secret_ciphertext'     => '',
			'has_polling_secret'            => '0',
			'action_key_id'                 => '',
			'action_private_key_ciphertext' => '',
			'next_poll_at'                  => '',
			'paused_at'                     => '',
		);
	}
}
