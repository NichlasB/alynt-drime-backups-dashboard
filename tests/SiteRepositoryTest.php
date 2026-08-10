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
}
