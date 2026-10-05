<?php
/**
 * Shared bootstrap for remote action repository tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once dirname( __DIR__, 2 ) . '/includes/class-remote-action-repository.php';
require_once __DIR__ . '/remote-action-repository-test-harness.php';

/**
 * Provides fake wpdb setup for remote action repository tests.
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_WPDB_Setup {
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
}