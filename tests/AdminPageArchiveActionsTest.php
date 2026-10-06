<?php
/**
 * Admin page archive action tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-actions-test-harness.php';

/**
 * Tests admin dashboard-local archive action behavior.
 */
class AdminPageArchiveActionsTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Admin_Action_Test_Case;

	/**
	 * Revoked records can be archived locally without contacting client sites.
	 *
	 * @return void
	 */
	public function test_archive_local_allows_revoked_records() {
		$this->set_valid_nonce( 'alynt_drime_backups_dashboard_archive_local' );

		$harness = $this->admin_action_harness();
		$harness->sites->site['enrollment_status'] = 'revoked';

		$_POST = array(
			'alynt_drime_backups_dashboard_action' => 'archive_local',
			'_wpnonce'                            => 'valid',
			'dashboard_site_id'                   => '42',
		);

		$result = $harness->handle_for_test();

		$this->assertIsArray( $result );
		$this->assertSame( 'archive_local', $result['action'] );
		$this->assertTrue( $result['success'] );
		$this->assertSame( array( 42 ), $harness->sites->archive_calls );
		$this->assertSame( 'archive_local', $harness->event_log->audit_calls[0]['action'] );
		$this->assertSame( 'succeeded', $harness->event_log->audit_calls[0]['outcome'] );
	}

	/**
	 * Active enrolled records cannot be hidden with archive controls.
	 *
	 * @return void
	 */
	public function test_archive_local_rejects_active_records() {
		$this->set_valid_nonce( 'alynt_drime_backups_dashboard_archive_local' );

		$harness = $this->admin_action_harness();

		$_POST = array(
			'alynt_drime_backups_dashboard_action' => 'archive_local',
			'_wpnonce'                            => 'valid',
			'dashboard_site_id'                   => '42',
		);

		$result = $harness->handle_for_test();

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'site_archive_not_allowed', $result->get_error_code() );
		$this->assertSame( array(), $harness->sites->archive_calls );
		$this->assertSame( 'failed', $harness->event_log->audit_calls[0]['outcome'] );
	}

	/**
	 * Archived records can be unarchived locally without restoring credentials.
	 *
	 * @return void
	 */
	public function test_unarchive_local_delegates_to_repository() {
		$this->set_valid_nonce( 'alynt_drime_backups_dashboard_unarchive_local' );

		$harness = $this->admin_action_harness();
		$harness->sites->site['enrollment_status'] = 'revoked';
		$harness->sites->site['archived_at']        = '2026-09-19 18:30:00';

		$_POST = array(
			'alynt_drime_backups_dashboard_action' => 'unarchive_local',
			'_wpnonce'                            => 'valid',
			'dashboard_site_id'                   => '42',
		);

		$result = $harness->handle_for_test();

		$this->assertIsArray( $result );
		$this->assertSame( 'unarchive_local', $result['action'] );
		$this->assertTrue( $result['success'] );
		$this->assertSame( array( 42 ), $harness->sites->unarchive_calls );
		$this->assertSame( 'unarchive_local', $harness->event_log->audit_calls[0]['action'] );
	}
}
