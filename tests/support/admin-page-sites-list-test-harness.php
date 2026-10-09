<?php
/**
 * Admin Sites-list test harness.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-sites-list-context.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-sites-list.php';
require_once __DIR__ . '/admin-page-sites-list-wordpress-shims.php';
require_once __DIR__ . '/admin-page-sites-list-exposure-methods.php';
require_once __DIR__ . '/admin-page-sites-list-test-doubles.php';

/**
 * Harness exposing private Sites-list helpers.
 */
class Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Test_Harness {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Exposure_Methods;

	/**
	 * Fake sites repository.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Test_Sites
	 */
	public $sites;

	/**
	 * Fake snapshots repository.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Test_Snapshots
	 */
	public $snapshots;

	/**
	 * Fake classifier.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Test_Classifier
	 */
	public $classifier;

	/**
	 * Request-local Sites-list cache.
	 *
	 * @var array<string,array<string,mixed>>
	 */
	private $site_status_context = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->sites      = new Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Test_Sites();
		$this->snapshots  = new Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Test_Snapshots();
		$this->classifier = new Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Test_Classifier();
	}
}
