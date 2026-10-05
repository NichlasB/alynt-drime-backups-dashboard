<?php
/**
 * Shared bootstrap for remote action dispatcher tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

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

if ( ! function_exists( 'home_url' ) ) {
	/**
	 * Test home_url shim.
	 *
	 * @param string      $path Path.
	 * @param string|null $scheme Scheme.
	 * @return string
	 */
	function home_url( $path = '', $scheme = null ) {
		unset( $scheme );

		return 'https://control.sitesmanage.com' . $path;
	}
}

require_once __DIR__ . '/remote-action-dispatcher-test-harness.php';

/**
 * Shared fake wpdb setup for dispatcher tests.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Dispatcher_WPDB_Setup {
	/**
	 * Previous wpdb.
	 *
	 * @var mixed
	 */
	private $previous_wpdb;

	/**
	 * Fake wpdb.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Test_Dispatcher_WPDB
	 */
	private $wpdb;

	protected function setUp(): void {
		parent::setUp();

		global $wpdb;

		$this->previous_wpdb = $wpdb;
		$this->wpdb          = new Alynt_Drime_Backups_Dashboard_Test_Dispatcher_WPDB();
		$this->wpdb->site    = $this->site_row();
		$this->wpdb->snapshot = $this->snapshot_row();
		$wpdb                = $this->wpdb;
	}

	protected function tearDown(): void {
		global $wpdb;

		$wpdb = $this->previous_wpdb;

		parent::tearDown();
	}
}
