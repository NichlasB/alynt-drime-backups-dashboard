<?php
/**
 * Remote action repository tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/remote-action-repository-test-bootstrap.php';

/**
 * Tests dashboard-owned remote action request and state writes.
 */
class RemoteActionRepositoryTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Test_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_WPDB_Setup;

	/**
	 * Create request stores bounded, redacted dashboard-owned data.
	 *
	 * @return void
	 */
	public function test_create_request_stores_redacted_action_context() {
		$repository = $this->remote_action_repository();
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
		$repository = $this->remote_action_repository();

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
		$repository = $this->remote_action_repository();

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

}