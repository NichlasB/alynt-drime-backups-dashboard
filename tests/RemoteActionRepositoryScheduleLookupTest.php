<?php
/**
 * Remote action repository schedule lookup tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/remote-action-repository-test-bootstrap.php';

/**
 * Tests schedule preview/apply lookup and site-scoped query behavior.
 */
class RemoteActionRepositoryScheduleLookupTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Test_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_WPDB_Setup;

	/**
	 * Expired schedule previews cannot be reused for schedule apply.
	 *
	 * @return void
	 */
	public function test_fresh_schedule_preview_rejects_expired_preview() {
		$repository = $this->remote_action_repository();
		$preview_id = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';

		$this->wpdb->row = $this->expired_schedule_preview_row( $preview_id );

		$result = $repository->fresh_schedule_preview_for_apply(
			44,
			$preview_id,
			$this->schedule_apply_lookup_capabilities(),
			'2026-08-20 12:06:00'
		);

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'schedule_apply_preview_expired', $result->get_error_code() );
		$this->assertStringContainsString( 'WHERE public_id = %s AND dashboard_site_id = %d', $this->wpdb->last_query );
		$this->assertSame( array( $preview_id, 44 ), $this->wpdb->prepared_args );
	}

	/**
	 * Successful schedule apply rows can provide bounded rollback-preview request data.
	 *
	 * @return void
	 */
	public function test_successful_schedule_apply_for_rollback_preview_returns_safe_request_data() {
		$repository = $this->remote_action_repository();
		$apply_id   = '33333333-3333-4333-8333-333333333333';

		$this->wpdb->row = $this->successful_schedule_apply_row( $apply_id );

		$result = $repository->successful_schedule_apply_for_rollback_preview(
			44,
			$apply_id,
			$this->schedule_rollback_preview_lookup_capabilities(),
			'2026-09-15 19:00:00'
		);

		$this->assertIsArray( $result );
		$this->assertSame( $apply_id, $result['source_apply_action_id'] );
		$this->assertSame( str_repeat( 'b', 64 ), $result['rollback_metadata_fingerprint'] );
		$this->assertSame( 'alynt_scan_upload', $result['schedule_id'] );
		$this->assertSame( 'every_15_minutes', $result['previous_cadence'] );
		$this->assertSame( 'every_30_minutes', $result['applied_cadence'] );
		$this->assertSame( 1, $result['capability_version'] );
		$this->assertStringContainsString( 'WHERE public_id = %s AND dashboard_site_id = %d', $this->wpdb->last_query );
		$this->assertSame( array( $apply_id, 44 ), $this->wpdb->prepared_args );
	}

	/**
	 * Lookup queries remain scoped to one site.
	 *
	 * @return void
	 */
	public function test_latest_and_recent_queries_are_site_scoped() {
		$repository       = $this->remote_action_repository();
		$this->wpdb->row  = array( 'id' => 9, 'dashboard_site_id' => 44 );
		$this->wpdb->rows = array( array( 'id' => 9, 'dashboard_site_id' => 44 ) );

		$this->assertSame( $this->wpdb->row, $repository->latest_for_site( 44 ) );
		$this->assertStringContainsString( 'WHERE dashboard_site_id = %d', $this->wpdb->last_query );
		$this->assertSame( array( 44 ), $this->wpdb->prepared_args );

		$this->assertSame( $this->wpdb->rows, $repository->recent_for_site( 44, 500 ) );
		$this->assertStringContainsString( 'ORDER BY requested_at DESC, id DESC', $this->wpdb->last_query );
		$this->assertSame( array( 44, 50 ), $this->wpdb->prepared_args );
	}
}
