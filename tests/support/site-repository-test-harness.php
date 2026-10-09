<?php
/**
 * Site repository test harness.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/site-repository-wordpress-shims.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-storage.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-site-repository-reads.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-site-repository-local-state-writes.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-site-repository-writes.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-site-repository-runtime-writes.php';
require_once dirname( __DIR__, 2 ) . '/includes/class-site-repository.php';
require_once __DIR__ . '/site-repository-wpdb-double.php';

/**
 * Shared site repository test setup.
 */
trait Alynt_Drime_Backups_Dashboard_Site_Repository_Test_Case {
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
}
