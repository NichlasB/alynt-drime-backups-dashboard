<?php
/**
 * Admin page action test harness.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! function_exists( 'home_url' ) ) {
	/**
	 * Test home_url shim.
	 *
	 * @param string $path   Path.
	 * @param string $scheme Scheme.
	 * @return string
	 */
	function home_url( $path = '', $scheme = null ) {
		unset( $scheme );

		return 'https://control.sitesmanage.com' . $path;
	}
}

if ( ! function_exists( 'wp_verify_nonce' ) ) {
	/**
	 * Test wp_verify_nonce shim.
	 *
	 * @param string $nonce  Nonce.
	 * @param string $action Action.
	 * @return bool|int
	 */
	function wp_verify_nonce( $nonce, $action ) {
		global $alynt_drime_backups_dashboard_test_nonce_action;
		global $alynt_drime_backups_dashboard_test_nonce_value;

		return $nonce === $alynt_drime_backups_dashboard_test_nonce_value && $action === $alynt_drime_backups_dashboard_test_nonce_action;
	}
}

if ( ! function_exists( 'get_current_user_id' ) ) {
	/**
	 * Test get_current_user_id shim.
	 *
	 * @return int
	 */
	function get_current_user_id() {
		global $alynt_drime_backups_dashboard_test_current_user_id;

		return (int) $alynt_drime_backups_dashboard_test_current_user_id;
	}
}

require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-actions.php';
require_once __DIR__ . '/admin-page-actions-test-doubles.php';

/**
 * Shared admin action test setup.
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Action_Test_Case {
	/**
	 * Original POST data.
	 *
	 * @var array<string,mixed>
	 */
	private $previous_post = array();

	/**
	 * Resets globals.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		global $alynt_drime_backups_dashboard_test_nonce_action;
		global $alynt_drime_backups_dashboard_test_nonce_value;
		global $alynt_drime_backups_dashboard_test_current_user_id;

		$this->previous_post = $_POST;
		$_POST              = array();

		$alynt_drime_backups_dashboard_test_nonce_action    = '';
		$alynt_drime_backups_dashboard_test_nonce_value     = '';
		$alynt_drime_backups_dashboard_test_current_user_id = 77;
	}

	/**
	 * Restores globals.
	 *
	 * @return void
	 */
	protected function tearDown(): void {
		$_POST = $this->previous_post;

		parent::tearDown();
	}

	/**
	 * Sets the valid nonce action/value for the next request.
	 *
	 * @param string $action Nonce action.
	 * @return void
	 */
	private function set_valid_nonce( $action ) {
		global $alynt_drime_backups_dashboard_test_nonce_action;
		global $alynt_drime_backups_dashboard_test_nonce_value;

		$alynt_drime_backups_dashboard_test_nonce_action = $action;
		$alynt_drime_backups_dashboard_test_nonce_value  = 'valid';
	}

	/**
	 * Creates an admin action harness.
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Enrollment_Manager|null $manager Optional manager.
	 * @return Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Harness
	 */
	private function admin_action_harness( $manager = null ) {
		if ( null === $manager ) {
			$manager = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Enrollment_Manager();
		}

		return new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Harness( $manager );
	}
}

/**
 * Minimal harness exposing the private trait action handler for tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Harness {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Actions;

	/**
	 * Enrollment manager.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Enrollment_Manager
	 */
	public $enrollment_manager;

	/**
	 * Remote action dispatcher.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Dispatcher
	 */
	public $remote_action_dispatcher;

	/**
	 * Poller.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Poller
	 */
	public $poller;

	/**
	 * Event log.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Event_Log
	 */
	public $event_log;

	/**
	 * Site repository.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Sites
	 */
	public $sites;

	/**
	 * Constructor.
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Enrollment_Manager $enrollment_manager Enrollment manager.
	 */
	public function __construct( $enrollment_manager ) {
		$this->enrollment_manager       = $enrollment_manager;
		$this->remote_action_dispatcher = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Dispatcher();
		$this->poller                   = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Poller();
		$this->event_log                = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Event_Log();
		$this->sites                    = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Sites();
	}

	/**
	 * Exposes the private action handler.
	 *
	 * @return array<string,mixed>|WP_Error|null
	 */
	public function handle_for_test() {
		return $this->handle_post_action();
	}
}
