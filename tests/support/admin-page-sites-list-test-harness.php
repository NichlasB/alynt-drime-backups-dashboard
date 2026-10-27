<?php
/**
 * Admin Sites-list test harness.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once dirname( __DIR__, 2 ) . '/includes/admin/traits/trait-admin-page-sites-list-context.php';
require_once dirname( __DIR__, 2 ) . '/includes/admin/traits/trait-admin-page-site-formatters.php';
require_once dirname( __DIR__, 2 ) . '/includes/admin/traits/trait-admin-page-sites-list.php';
require_once dirname( __DIR__, 2 ) . '/includes/admin/traits/trait-admin-page-sites-table.php';
require_once dirname( __DIR__, 2 ) . '/includes/admin/traits/trait-admin-page-status-formatters.php';
require_once dirname( __DIR__, 2 ) . '/includes/admin/traits/trait-admin-page-attention.php';
require_once dirname( __DIR__, 2 ) . '/includes/admin/traits/trait-admin-page-time-formatters.php';
require_once __DIR__ . '/admin-page-sites-list-wordpress-shims.php';
require_once __DIR__ . '/admin-page-sites-list-exposure-methods.php';
require_once __DIR__ . '/admin-page-sites-list-test-doubles.php';

/**
 * Harness exposing private Sites-list helpers.
 */
class Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Test_Harness {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Shell;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Site_Formatters;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_Table;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Status_Formatters;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Sites_List_Exposure_Methods;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Attention;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Time_Formatters;

	const MENU_SLUG = 'alynt-drime-backups-dashboard';

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

	/**
	 * Renders a stub Check Now form for Attention shell tests.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @param int                 $site_id Site ID.
	 * @param bool                $primary Whether the button is primary.
	 * @return void
	 */
	protected function render_check_status_form( array $site, $site_id, $primary ) {
		echo '<form class="adbd-check-now-stub"></form>';
	}

	/** Isolates only unrelated row hints/forms; unexpected dependencies must fail. */
	public function __call( $name, array $arguments ) {
		if ( in_array( $name, array( 'retained_record_removal_row_hint', 'next_poll_html', 'render_polling_pause_form', 'request_backup_now_row_hint', 'schedule_management_row_hint' ), true ) ) {
			return '';
		}
		throw new BadMethodCallException( $name );
	}
}
