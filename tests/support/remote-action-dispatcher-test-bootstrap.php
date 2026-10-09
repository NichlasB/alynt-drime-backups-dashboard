<?php
/**
 * Shared bootstrap for remote action dispatcher tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/remote-action-dispatcher-wordpress-shims.php';
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
