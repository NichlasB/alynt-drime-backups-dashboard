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

		$this->wpdb->row = array(
			'id'                    => 321,
			'public_id'             => $preview_id,
			'dashboard_site_id'     => 44,
			'action_type'           => 'schedule_preview',
			'state'                 => 'succeeded',
			'completed_at'          => '2026-08-20 12:00:00',
			'redacted_context_json' => wp_json_encode(
				array(
					'schedule_preview' => array(
						'preview_action_id'    => $preview_id,
						'preview_fingerprint'  => str_repeat( 'a', 64 ),
						'schedule_id'          => 'alynt_scan_upload',
						'current_cadence'      => 'every_15_minutes',
						'proposed_cadence'     => 'every_30_minutes',
						'capability_version'   => 1,
						'preview_expires_at'   => '2026-08-20T12:05:00+00:00',
						'would_change'         => true,
						'apply_supported'      => true,
						'rollback_supported'   => false,
					),
				)
			),
		);

		$result = $repository->fresh_schedule_preview_for_apply(
			44,
			$preview_id,
			array(
				'allowed_actions'      => array( 'scan_upload_now', 'schedule_preview', 'schedule_apply' ),
				'schedule_management' => array(
					'schedules'         => array(
						array(
							'id'                 => 'alynt_scan_upload',
							'apply_supported'    => true,
							'supported_cadences' => array( 'every_30_minutes' ),
						),
					),
					'apply_supported'   => true,
					'preview_supported' => true,
				),
			),
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

		$this->wpdb->row = array(
			'id'                    => 321,
			'public_id'             => $apply_id,
			'dashboard_site_id'     => 44,
			'action_type'           => 'schedule_apply',
			'state'                 => 'succeeded',
			'completed_at'          => '2026-09-15 18:24:12',
			'redacted_context_json' => wp_json_encode(
				array(
					'schedule_apply' => array(
						'schedule_id'        => 'alynt_scan_upload',
						'previous_cadence'   => 'every_15_minutes',
						'applied_cadence'    => 'every_30_minutes',
						'capability_version' => 1,
						'rollback_metadata'  => array(
							'captured'                      => true,
							'source_action_id'              => $apply_id,
							'schedule_id'                   => 'alynt_scan_upload',
							'previous_cadence'              => 'every_15_minutes',
							'applied_cadence'               => 'every_30_minutes',
							'rollback_metadata_fingerprint' => str_repeat( 'b', 64 ),
							'expires_at'                    => '2026-09-15T19:24:12+00:00',
						),
					),
				)
			),
		);

		$result = $repository->successful_schedule_apply_for_rollback_preview(
			44,
			$apply_id,
			array(
				'enabled'          => true,
				'sodium_available' => true,
				'allowed_actions'  => array( 'scan_upload_now', 'schedule_preview', 'schedule_apply', 'schedule_rollback_preview' ),
				'schedule_management' => array(
					'enabled'                    => true,
					'rollback_preview_supported' => true,
					'rollback_supported'         => false,
					'schedules'                  => array(
						array(
							'schedule_id'                => 'alynt_scan_upload',
							'manageable'                 => true,
							'rollback_preview_supported' => true,
							'rollback_supported'         => false,
						),
					),
				),
			),
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