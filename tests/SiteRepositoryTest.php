<?php
/**
 * Site repository tests.
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
	 * @param bool   $gmt  Whether to use GMT.
	 * @return string
	 */
	function current_time( $type, $gmt = false ) {
		unset( $type, $gmt );

		return '2099-01-01 00:00:00';
	}
}

require_once dirname( __DIR__ ) . '/includes/class-storage.php';
require_once dirname( __DIR__ ) . '/includes/class-site-repository.php';

/**
 * Fake wpdb for site repository tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Site_WPDB {
	/**
	 * Table prefix.
	 *
	 * @var string
	 */
	public $prefix = 'wp_';

	/**
	 * Prepared arguments.
	 *
	 * @var array<int,mixed>
	 */
	public $prepared_args = array();

	/**
	 * Last query.
	 *
	 * @var string
	 */
	public $last_query = '';

	/**
	 * Last output mode.
	 *
	 * @var string
	 */
	public $last_output = '';

	/**
	 * Result returned by update().
	 *
	 * @var int|false
	 */
	public $update_result = 1;

	/**
	 * Last updated table.
	 *
	 * @var string
	 */
	public $updated_table = '';

	/**
	 * Last updated data.
	 *
	 * @var array<string,mixed>
	 */
	public $updated_data = array();

	/**
	 * Last update where clause.
	 *
	 * @var array<string,mixed>
	 */
	public $updated_where = array();

	/**
	 * Row returned by get_row().
	 *
	 * @var array<string,mixed>|null
	 */
	public $row = null;

	/**
	 * Prepares a query.
	 *
	 * @param string $query Query.
	 * @param mixed  ...$args Arguments.
	 * @return string
	 */
	public function prepare( $query, ...$args ) {
		$this->prepared_args = $args;

		return $query;
	}

	/**
	 * Gets a fake row.
	 *
	 * @param string $query  Query.
	 * @param string $output Output mode.
	 * @return array<string,mixed>|null
	 */
	public function get_row( $query, $output = OBJECT ) {
		$this->last_query  = $query;
		$this->last_output = $output;

		return $this->row;
	}

	/**
	 * Updates a fake row.
	 *
	 * @param string              $table        Table.
	 * @param array<string,mixed> $data         Data.
	 * @param array<string,mixed> $where        Where clause.
	 * @param array<int,string>   $format       Data format.
	 * @param array<int,string>   $where_format Where format.
	 * @return int|false
	 */
	public function update( $table, $data, $where, $format = null, $where_format = null ) {
		unset( $format, $where_format );

		$this->updated_table = $table;
		$this->updated_data  = $data;
		$this->updated_where = $where;

		return $this->update_result;
	}
}

/**
 * Tests dashboard site repository behavior.
 */
class SiteRepositoryTest extends TestCase {
	/**
	 * Previous wpdb.
	 *
	 * @var mixed
	 */
	private $previous_wpdb;

	/**
	 * Fake wpdb.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Test_Site_WPDB
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
		$this->wpdb          = new Alynt_Drime_Backups_Dashboard_Test_Site_WPDB();
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
	 * Active pending lookup is bounded to same-origin, pending, non-expired pairings.
	 *
	 * @return void
	 */
	public function test_active_pending_lookup_uses_origin_pending_status_and_expiry_window() {
		$this->wpdb->row = array(
			'id'              => 44,
			'expected_origin' => 'https://client.example.com',
		);

		$repository = new Alynt_Drime_Backups_Dashboard_Site_Repository();
		$result     = $repository->get_active_pending_by_expected_origin(
			'https://client.example.com',
			'2099-01-01 00:05:00'
		);

		$this->assertSame( $this->wpdb->row, $result );
		$this->assertSame( ARRAY_A, $this->wpdb->last_output );
		$this->assertStringContainsString( 'FROM wp_alynt_drime_dashboard_sites', $this->wpdb->last_query );
		$this->assertStringContainsString( 'expected_origin = %s', $this->wpdb->last_query );
		$this->assertStringContainsString( 'enrollment_status = %s', $this->wpdb->last_query );
		$this->assertStringContainsString( 'pairing_expires_at > %s', $this->wpdb->last_query );
		$this->assertStringContainsString( 'LIMIT 1', $this->wpdb->last_query );
		$this->assertSame(
			array(
				'https://client.example.com',
				'pending',
				'2099-01-01 00:05:00',
			),
			$this->wpdb->prepared_args
		);
	}

	/**
	 * Active pending lookup falls back to current UTC time when no time is supplied.
	 *
	 * @return void
	 */
	public function test_active_pending_lookup_uses_current_time_when_not_supplied() {
		$repository = new Alynt_Drime_Backups_Dashboard_Site_Repository();
		$result     = $repository->get_active_pending_by_expected_origin( 'https://client.example.com' );

		$this->assertNull( $result );
		$this->assertSame( '2099-01-01 00:00:00', $this->wpdb->prepared_args[2] );
	}

	/**
	 * Revocation fails when the target row no longer exists or was already changed.
	 *
	 * @return void
	 */
	public function test_revoke_local_requires_changed_row() {
		$this->wpdb->update_result = 0;

		$repository = new Alynt_Drime_Backups_Dashboard_Site_Repository();
		$result     = $repository->revoke_local( 123 );

		$this->assertFalse( $result );
		$this->assertSame( 'wp_alynt_drime_dashboard_sites', $this->wpdb->updated_table );
		$this->assertSame( array( 'id' => 123 ), $this->wpdb->updated_where );
		$this->assertSame( 'revoked', $this->wpdb->updated_data['enrollment_status'] );
	}

	/**
	 * Enrollment completion fails when another request already consumed the pending row.
	 *
	 * @return void
	 */
	public function test_complete_enrollment_pending_first_poll_requires_changed_pending_row() {
		$this->wpdb->update_result = 0;

		$repository = new Alynt_Drime_Backups_Dashboard_Site_Repository();
		$result     = $repository->complete_enrollment_pending_first_poll(
			123,
			array(
				'site_uuid'                 => 'site-uuid',
				'polling_key_id'            => 'key-id',
				'polling_secret_ciphertext' => 'ciphertext',
				'plugin_version'            => '0.1.0',
				'payload_schema_version'    => 1,
			)
		);

		$this->assertFalse( $result );
		$this->assertSame( 'wp_alynt_drime_dashboard_sites', $this->wpdb->updated_table );
		$this->assertSame(
			array(
				'id'                => 123,
				'enrollment_status' => 'pending',
			),
			$this->wpdb->updated_where
		);
		$this->assertSame( 'awaiting_first_poll', $this->wpdb->updated_data['enrollment_status'] );
	}
}
