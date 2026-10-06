<?php
/**
 * Admin site-detail local record rendering tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-polling-state-rendering-bootstrap.php';

/**
 * Tests local polling controls, attention history, and archive guidance.
 */
class AdminPageSiteDetailLocalRecordRenderingTest extends TestCase {
	/**
	 * Site detail polling control explains the local-only boundary.
	 *
	 * @return void
	 */
	public function test_site_detail_polling_pause_panel_explains_local_only_boundary() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$site    = array(
			'id'                 => 7,
			'enrollment_status'  => 'active',
			'polling_key_id'     => 'key-id',
			'has_polling_secret' => '1',
			'paused_at'          => '',
		);
		$html    = $harness->pause_panel_html( $site );

		$this->assertStringContainsString( 'Scheduled Polling Control', $html );
		$this->assertStringContainsString( 'changes only this dashboard record', $html );
		$this->assertStringContainsString( 'does not contact the client site', $html );
		$this->assertStringContainsString( 'Pause Polling', $html );
	}

	/**
	 * Revoked Site Detail guidance explains retention and re-enrollment without removal controls.
	 *
	 * @return void
	 */
	public function test_revoked_site_detail_guidance_explains_local_retention() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$html    = $harness->revoked_record_guidance_html(
			array(
				'id'                => 7,
				'enrollment_status' => 'revoked',
			)
		);

		$this->assertStringContainsString( 'Revoked Local Dashboard Record', $html );
		$this->assertStringContainsString( 'retained locally for audit/history', $html );
		$this->assertStringContainsString( 'create a new pairing token and complete client-site opt-in', $html );
		$this->assertStringContainsString( 'Permanent local removal is not available in this release', $html );
		$this->assertStringNotContainsString( '<form', $html );
	}

	/**
	 * Active Site Detail screens do not show revoked-record guidance.
	 *
	 * @return void
	 */
	public function test_active_site_detail_does_not_show_revoked_guidance() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$html    = $harness->revoked_record_guidance_html(
			array(
				'id'                => 7,
				'enrollment_status' => 'active',
			)
		);

		$this->assertSame( '', $html );
	}

	/**
	 * Revoked Site Detail screens expose local archive controls.
	 *
	 * @return void
	 */
	public function test_revoked_site_detail_can_show_archive_control() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$html    = $harness->archive_record_panel_html(
			array(
				'id'                => 7,
				'enrollment_status' => 'revoked',
				'archived_at'       => '',
			)
		);

		$this->assertStringContainsString( 'Local Record Visibility', $html );
		$this->assertStringContainsString( 'Archive Local Record', $html );
		$this->assertStringContainsString( 'value="archive_local"', $html );
		$this->assertStringContainsString( 'does not delete data, contact the client site, change backups, alter Drime, or reuse credentials', $html );
	}

	/**
	 * Archived Site Detail screens expose local unarchive controls.
	 *
	 * @return void
	 */
	public function test_archived_site_detail_can_show_unarchive_control() {
		$harness = new Alynt_Drime_Backups_Dashboard_Polling_State_Rendering_Test_Harness();
		$html    = $harness->archive_record_panel_html(
			array(
				'id'                => 7,
				'enrollment_status' => 'revoked',
				'archived_at'       => '2026-09-19 18:30:00',
			)
		);

		$this->assertStringContainsString( 'This dashboard record is archived locally', $html );
		$this->assertStringContainsString( 'Unarchive Local Record', $html );
		$this->assertStringContainsString( 'value="unarchive_local"', $html );
		$this->assertStringNotContainsString( 'restore credentials', strtolower( $html ) );
	}

}
