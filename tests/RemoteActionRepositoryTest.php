<?php
/**
 * Remote action repository tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

if ( ! defined( 'ARRAY_A' ) ) {
	define( 'ARRAY_A', 'ARRAY_A' );
}

if ( ! function_exists( 'current_time' ) ) {
	/**
	 * Test current_time shim.
	 *
	 * @param string $type Type.
	 * @param bool   $gmt GMT.
	 * @return string
	 */
	function current_time( $type, $gmt = false ) {
		unset( $type, $gmt );
		return '2099-01-01 00:00:00';
	}
}

/**
 * Fake wpdb for remote action repository tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Remote_Action_WPDB {
	/**
	 * Table prefix.
	 *
	 * @var string
	 */
	public $prefix = 'wp_';

	/**
	 * Insert ID.
	 *
	 * @var int
	 */
	public $insert_id = 321;

	/**
	 * Last insert table.
	 *
	 * @var string
	 */
	public $inserted_table = '';

	/**
	 * Last insert data.
	 *
	 * @var array<string,mixed>
	 */
	public $inserted_data = array();

	/**
	 * Last update data.
	 *
	 * @var array<string,mixed>
	 */
	public $updated_data = array();

	/**
	 * Last update where.
	 *
	 * @var array<string,mixed>
	 */
	public $updated_where = array();

	/**
	 * Prepared query.
	 *
	 * @var string
	 */
	public $last_query = '';

	/**
	 * Prepared args.
	 *
	 * @var array<int,mixed>
	 */
	public $prepared_args = array();

	/**
	 * Result row.
	 *
	 * @var array<string,mixed>|null
	 */
	public $row = null;

	/**
	 * Result rows.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	public $rows = array();

	/**
	 * Insert shim.
	 *
	 * @param string             $table Table.
	 * @param array<string,mixed> $data Data.
	 * @param array<int,string>  $format Format.
	 * @return int|false
	 */
	public function insert( $table, $data, $format = array() ) {
		unset( $format );
		$this->inserted_table = $table;
		$this->inserted_data  = $data;

		return 1;
	}

	/**
	 * Update shim.
	 *
	 * @param string             $table Table.
	 * @param array<string,mixed> $data Data.
	 * @param array<string,mixed> $where Where.
	 * @return int
	 */
	public function update( $table, $data, $where ) {
		unset( $table );
		$this->updated_data  = $data;
		$this->updated_where = $where;

		return 1;
	}

	/**
	 * Query preparation shim.
	 *
	 * @param string $query Query.
	 * @param mixed  ...$args Args.
	 * @return string
	 */
	public function prepare( $query, ...$args ) {
		$this->last_query    = $query;
		$this->prepared_args = $args;

		return $query;
	}

	/**
	 * Row retrieval shim.
	 *
	 * @param string $query Query.
	 * @param string $output Output type.
	 * @return array<string,mixed>|null
	 */
	public function get_row( $query, $output = ARRAY_A ) {
		unset( $output );
		$this->last_query = $query;

		return $this->row;
	}

	/**
	 * Row list retrieval shim.
	 *
	 * @param string $query Query.
	 * @param string $output Output type.
	 * @return array<int,array<string,mixed>>
	 */
	public function get_results( $query, $output = ARRAY_A ) {
		unset( $query, $output );

		return $this->rows;
	}

	/**
	 * Generic query shim.
	 *
	 * @param string $query Query.
	 * @return int
	 */
	public function query( $query ) {
		unset( $query );

		return 2;
	}
}

/**
 * Tests dashboard-owned remote action storage.
 */
class RemoteActionRepositoryTest extends TestCase {
	/**
	 * Previous wpdb.
	 *
	 * @var mixed
	 */
	private $previous_wpdb;

	/**
	 * Fake wpdb.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Test_Remote_Action_WPDB
	 */
	private $wpdb;

	/**
	 * Sets fake wpdb.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		global $wpdb;

		$this->previous_wpdb = $wpdb;
		$this->wpdb          = new Alynt_Drime_Backups_Dashboard_Test_Remote_Action_WPDB();
		$wpdb                = $this->wpdb;
	}

	/**
	 * Restores wpdb.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		global $wpdb;

		$wpdb = $this->previous_wpdb;

		parent::tearDown();
	}

	/**
	 * Create request stores bounded, redacted dashboard-owned data.
	 *
	 * @return void
	 */
	public function test_create_request_stores_redacted_action_context() {
		$repository = new Alynt_Drime_Backups_Dashboard_Remote_Action_Repository();
		$action_id  = $repository->create_request(
			44,
			'scan_upload_now',
			7,
			'idempotency:bad key',
			'ak_123',
			'2026-08-20 12:05:00',
			str_repeat( 'a', 64 ),
			array(
				'operator_note' => '<b>Manual check</b>',
				'client_path'   => '/private/path',
				'found'         => 3,
			),
			'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'
		);

		$this->assertSame( 321, $action_id );
		$this->assertSame( 'wp_alynt_drime_dashboard_actions', $this->wpdb->inserted_table );
		$this->assertSame( 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', $this->wpdb->inserted_data['public_id'] );
		$this->assertSame( 44, $this->wpdb->inserted_data['dashboard_site_id'] );
		$this->assertSame( 'scan_upload_now', $this->wpdb->inserted_data['action_type'] );
		$this->assertSame( 'queued_for_dispatch', $this->wpdb->inserted_data['state'] );
		$this->assertSame( 'idempotencybadkey', $this->wpdb->inserted_data['idempotency_key'] );
		$this->assertSame( str_repeat( 'a', 64 ), $this->wpdb->inserted_data['request_fingerprint'] );

		$context = json_decode( $this->wpdb->inserted_data['redacted_context_json'], true );

		$this->assertSame( '<b>Manual check</b>', $context['operator_note'] );
		$this->assertSame( '[redacted]', $context['client_path'] );
		$this->assertSame( 3, $context['found'] );
	}

	/**
	 * Unknown states fall back to a non-dispatched queue state.
	 *
	 * @return void
	 */
	public function test_mark_state_sanitizes_unknown_state() {
		$repository = new Alynt_Drime_Backups_Dashboard_Remote_Action_Repository();

		$this->assertTrue( $repository->mark_state( 321, 'restore_now', 'raw code', 'Done', 30 ) );
		$this->assertSame( array( 'id' => 321 ), $this->wpdb->updated_where );
		$this->assertSame( 'queued_for_dispatch', $this->wpdb->updated_data['state'] );
		$this->assertSame( 'rawcode', $this->wpdb->updated_data['result_code'] );
		$this->assertArrayNotHasKey( 'completed_at', $this->wpdb->updated_data );
	}

	/**
	 * Client reports store support-safe reconciliation fields.
	 *
	 * @return void
	 */
	public function test_mark_client_report_stores_sanitized_reconciliation_fields() {
		$repository = new Alynt_Drime_Backups_Dashboard_Remote_Action_Repository();

		$this->assertTrue(
			$repository->mark_client_report(
				321,
				array(
					'state'          => 'succeeded',
					'updated_at'     => '2026-08-20T12:04:00+00:00',
					'result_code'    => 'action_succeeded',
					'result_summary' => '<b>Scan completed safely.</b>',
					'counts'         => array(
						'found'            => 4,
						'queued'           => 1,
						'already_known'    => 2,
						'upload_attempted' => 1,
						'failed'           => -1,
						'local_path'       => '/private/path',
					),
				),
				'2026-08-20 12:05:00'
			)
		);

		$this->assertSame( array( 'id' => 321 ), $this->wpdb->updated_where );
		$this->assertSame( 'succeeded', $this->wpdb->updated_data['state'] );
		$this->assertSame( 'succeeded', $this->wpdb->updated_data['client_state'] );
		$this->assertSame( 'action_succeeded', $this->wpdb->updated_data['client_result_code'] );
		$this->assertSame( '<b>Scan completed safely.</b>', $this->wpdb->updated_data['client_result_summary'] );
		$this->assertSame( '2026-08-20 12:04:00', $this->wpdb->updated_data['client_updated_at'] );
		$this->assertSame( '2026-08-20 12:05:00', $this->wpdb->updated_data['reconciled_at'] );
		$this->assertArrayHasKey( 'completed_at', $this->wpdb->updated_data );

		$counts = json_decode( $this->wpdb->updated_data['client_counts_json'], true );

		$this->assertSame( 4, $counts['found'] );
		$this->assertSame( 0, $counts['failed'] );
		$this->assertArrayNotHasKey( 'local_path', $counts );
	}

	/**
	 * Client schedule apply reports preserve support-safe apply details.
	 *
	 * @return void
	 */
	public function test_mark_client_report_preserves_schedule_apply_details() {
		$repository      = new Alynt_Drime_Backups_Dashboard_Remote_Action_Repository();
		$this->wpdb->row = array(
			'id'                    => 321,
			'redacted_context_json' => wp_json_encode( array() ),
		);

		$this->assertTrue(
			$repository->mark_client_report(
				321,
				array(
					'state'          => 'succeeded',
					'result_code'    => 'schedule_apply_succeeded',
					'result_summary' => 'Schedule apply completed for Alynt scan/upload.',
					'schedule_apply' => array(
						'schedule_id'          => 'alynt_scan_upload',
						'label'                => 'Alynt scan/upload',
						'owner'                => 'alynt_uploader',
						'capability_version'   => 1,
						'preview_action_id'    => '22222222-2222-4222-8222-222222222222',
						'preview_fingerprint'  => str_repeat( 'a', 64 ),
						'proposed_cadence'     => 'every_30_minutes',
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
							'source_action_id'                    => '33333333-3333-4333-8333-333333333333',
							'source_preview_action_id'            => '22222222-2222-4222-8222-222222222222',
							'schedule_id'                         => 'alynt_scan_upload',
							'owner'                               => 'alynt_uploader',
							'previous_cadence'                    => 'every_15_minutes',
							'applied_cadence'                     => 'every_30_minutes',
							'previous_next_run_at'                => '2026-09-15T18:30:03+00:00',
							'applied_next_run_at'                 => '2026-09-15T18:53:55+00:00',
							'current_schedule_fingerprint_before' => str_repeat( 'b', 64 ),
							'current_schedule_fingerprint_after'  => str_repeat( 'c', 64 ),
							'rollback_metadata_fingerprint'       => str_repeat( 'd', 64 ),
							'captured_at'                         => '2026-09-15T18:24:12+00:00',
							'expires_at'                          => '2026-09-15T19:24:12+00:00',
						),
					),
				),
				'2026-09-15 18:24:12'
			)
		);

		$context = json_decode( $this->wpdb->updated_data['redacted_context_json'], true );

		$this->assertSame( 'alynt_scan_upload', $context['schedule_apply']['schedule_id'] );
		$this->assertSame( 'every_15_minutes', $context['schedule_apply']['previous_cadence'] );
		$this->assertSame( 'every_30_minutes', $context['schedule_apply']['applied_cadence'] );
		$this->assertSame( '2026-09-15T18:53:55+00:00', $context['schedule_apply']['new_next_run_at'] );
		$this->assertSame( '22222222-2222-4222-8222-222222222222', $context['schedule_apply']['preview_action_id'] );
		$this->assertTrue( $context['schedule_apply']['changed'] );
		$this->assertFalse( $context['schedule_apply']['rollback_available'] );
		$this->assertTrue( $context['schedule_apply']['rollback_metadata']['captured'] );
		$this->assertFalse( $context['schedule_apply']['rollback_metadata']['available'] );
		$this->assertSame( 'schedule_rollback_runtime_not_implemented', $context['schedule_apply']['rollback_metadata']['reason'] );
		$this->assertSame( str_repeat( 'b', 64 ), $context['schedule_apply']['rollback_metadata']['current_schedule_fingerprint_before'] );
		$this->assertSame( str_repeat( 'c', 64 ), $context['schedule_apply']['rollback_metadata']['current_schedule_fingerprint_after'] );
		$this->assertSame( str_repeat( 'd', 64 ), $context['schedule_apply']['rollback_metadata']['rollback_metadata_fingerprint'] );
	}

	/**
	 * Client rollback-preview reports preserve support-safe preview details.
	 *
	 * @return void
	 */
	public function test_mark_client_report_preserves_schedule_rollback_preview_details() {
		$repository      = new Alynt_Drime_Backups_Dashboard_Remote_Action_Repository();
		$this->wpdb->row = array(
			'id'                    => 321,
			'redacted_context_json' => wp_json_encode( array() ),
		);

		$this->assertTrue(
			$repository->mark_client_report(
				321,
				array(
					'state'                     => 'succeeded',
					'result_code'               => 'schedule_rollback_preview_ready',
					'result_summary'            => 'Schedule rollback preview completed.',
					'schedule_rollback_preview' => array(
						'schedule_id'                           => 'alynt_scan_upload',
						'label'                                 => 'Alynt scan/upload',
						'owner'                                 => 'alynt_uploader',
						'current_cadence'                       => 'every_30_minutes',
						'applied_cadence'                       => 'every_30_minutes',
						'rollback_cadence'                      => 'every_15_minutes',
						'current_next_run_at'                   => '2026-09-15T18:53:55+00:00',
						'rollback_next_run_estimate_at'         => '2026-09-15T18:45:00+00:00',
						'would_change'                          => true,
						'rollback_apply_supported'              => true,
						'rollback_supported'                    => true,
						'preview_action_id'                     => '44444444-4444-4444-8444-444444444444',
						'preview_fingerprint'                   => str_repeat( 'a', 64 ),
						'source_apply_action_id'                => '33333333-3333-4333-8333-333333333333',
						'rollback_metadata_fingerprint'         => str_repeat( 'b', 64 ),
						'current_schedule_fingerprint'          => str_repeat( 'c', 64 ),
						'expected_current_schedule_fingerprint' => str_repeat( 'd', 64 ),
						'previous_schedule_fingerprint'         => str_repeat( 'e', 64 ),
						'capability_version'                    => 1,
						'preview_created_at'                    => '2026-09-15T18:35:00+00:00',
						'preview_expires_at'                    => '2026-09-15T18:50:00+00:00',
					),
				),
				'2026-09-15 18:35:12'
			)
		);

		$context = json_decode( $this->wpdb->updated_data['redacted_context_json'], true );

		$this->assertSame( 'alynt_scan_upload', $context['schedule_rollback_preview']['schedule_id'] );
		$this->assertSame( 'every_30_minutes', $context['schedule_rollback_preview']['current_cadence'] );
		$this->assertSame( 'every_15_minutes', $context['schedule_rollback_preview']['rollback_cadence'] );
		$this->assertTrue( $context['schedule_rollback_preview']['would_change'] );
		$this->assertFalse( $context['schedule_rollback_preview']['rollback_apply_supported'] );
		$this->assertFalse( $context['schedule_rollback_preview']['rollback_supported'] );
		$this->assertSame( '33333333-3333-4333-8333-333333333333', $context['schedule_rollback_preview']['source_apply_action_id'] );
		$this->assertSame( str_repeat( 'b', 64 ), $context['schedule_rollback_preview']['rollback_metadata_fingerprint'] );
	}

	/**
	 * Support summaries count rollback-readiness evidence without exposing details.
	 *
	 * @return void
	 */
	public function test_support_summary_counts_schedule_apply_rollback_metadata() {
		$repository      = new Alynt_Drime_Backups_Dashboard_Remote_Action_Repository();
		$this->wpdb->row = array(
			'total'                      => 4,
			'client_reconciled'          => 3,
			'stale'                      => 1,
			'awaiting_confirmation'      => 1,
			'schedule_apply'             => 2,
			'rollback_metadata_captured' => 1,
			'latest_updated_at'          => '2026-09-15 19:24:12',
		);

		$summary = $repository->support_summary();

		$this->assertSame( 4, $summary['total'] );
		$this->assertSame( 2, $summary['schedule_apply'] );
		$this->assertSame( 1, $summary['rollback_metadata'] );
		$this->assertStringContainsString( "action_type = 'schedule_apply'", $this->wpdb->last_query );
		$this->assertStringContainsString( 'rollback_metadata', $this->wpdb->last_query );
		$this->assertArrayNotHasKey( 'redacted_context_json', $summary );
	}

	/**
	 * Completed action retention cleanup is bounded and prepared.
	 *
	 * @return void
	 */
	public function test_cleanup_retention_deletes_completed_actions_in_bounded_batches() {
		$repository = new Alynt_Drime_Backups_Dashboard_Remote_Action_Repository();
		$cutoff     = gmdate( 'Y-m-d H:i:s', time() - ( 90 * 86400 ) );

		$this->assertSame( 2, $repository->cleanup_retention( 90, 500 ) );
		$this->assertStringContainsString( 'DELETE FROM wp_alynt_drime_dashboard_actions', $this->wpdb->last_query );
		$this->assertStringContainsString( 'completed_at IS NOT NULL', $this->wpdb->last_query );
		$this->assertStringContainsString( 'ORDER BY completed_at ASC, id ASC', $this->wpdb->last_query );
		$this->assertStringContainsString( 'LIMIT %d', $this->wpdb->last_query );
		$this->assertSame( array( $cutoff, 500 ), $this->wpdb->prepared_args );
	}

	/**
	 * Stale reconciliation is scoped to one dashboard site.
	 *
	 * @return void
	 */
	public function test_mark_unconfirmed_actions_stale_is_site_scoped() {
		$repository = new Alynt_Drime_Backups_Dashboard_Remote_Action_Repository();

		$this->assertSame( 2, $repository->mark_unconfirmed_actions_stale_for_site( 44, '2026-08-20 12:05:00' ) );
		$this->assertStringContainsString( "state = 'stale'", $this->wpdb->last_query );
		$this->assertStringContainsString( 'dashboard_site_id = %d', $this->wpdb->last_query );
		$this->assertStringContainsString( 'client_state IS NULL', $this->wpdb->last_query );
		$this->assertSame( 44, $this->wpdb->prepared_args[3] );
	}

	/**
	 * Expired schedule previews cannot be reused for schedule apply.
	 *
	 * @return void
	 */
	public function test_fresh_schedule_preview_rejects_expired_preview() {
		$repository = new Alynt_Drime_Backups_Dashboard_Remote_Action_Repository();
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
		$repository = new Alynt_Drime_Backups_Dashboard_Remote_Action_Repository();
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
		$repository       = new Alynt_Drime_Backups_Dashboard_Remote_Action_Repository();
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
