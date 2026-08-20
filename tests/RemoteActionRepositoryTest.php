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
		unset( $query, $output );

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
			)
		);

		$this->assertSame( 321, $action_id );
		$this->assertSame( 'wp_alynt_drime_dashboard_actions', $this->wpdb->inserted_table );
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
