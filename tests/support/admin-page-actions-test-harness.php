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
 * Fake enrollment manager for admin action tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Enrollment_Manager {
	/**
	 * Recorded calls.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	public $calls = array();

	/**
	 * Return value.
	 *
	 * @var array<string,mixed>|WP_Error
	 */
	public $result = array(
		'site_id'       => 123,
		'pairing_token' => 'adb1.test',
	);

	/**
	 * Records pending-site creation.
	 *
	 * @param array  $raw Raw pending site data.
	 * @param string $dashboard_origin Dashboard origin.
	 * @return array<string,mixed>|WP_Error
	 */
	public function create_pending_site( array $raw, $dashboard_origin ) {
		$this->calls[] = array(
			'raw'              => $raw,
			'dashboard_origin' => $dashboard_origin,
		);

		return $this->result;
	}
}

/**
 * Fake remote action dispatcher.
 */
class Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Dispatcher {
	/**
	 * Calls.
	 *
	 * @var array<int,array<string,int>>
	 */
	public $calls = array();

	/**
	 * Result.
	 *
	 * @var array<string,mixed>|WP_Error
	 */
	public $result = array(
		'action'       => 'request_backup_now',
		'remote_state' => 'accepted',
	);

	/**
	 * Records request.
	 *
	 * @param int $site_id Site ID.
	 * @param int $requested_by User ID.
	 * @return array<string,mixed>|WP_Error
	 */
	public function request_scan_upload_now( $site_id, $requested_by = 0 ) {
		$this->calls[] = array(
			'site_id'      => (int) $site_id,
			'requested_by' => (int) $requested_by,
		);

		return $this->result;
	}

	/**
	 * Records cleanup preview request.
	 *
	 * @param int $site_id Site ID.
	 * @param int $requested_by User ID.
	 * @return array<string,mixed>|WP_Error
	 */
	public function request_cleanup_preview( $site_id, $requested_by = 0 ) {
		$this->calls[] = array(
			'site_id'      => (int) $site_id,
			'requested_by' => (int) $requested_by,
		);

		return array(
			'action'       => 'cleanup_preview',
			'remote_state' => 'accepted',
		);
	}
}

/**
 * Fake poller.
 */
class Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Poller {
	/**
	 * Calls.
	 *
	 * @var array<int,int>
	 */
	public $calls = array();

	/**
	 * Checks status.
	 *
	 * @param int $site_id Site ID.
	 * @return array<string,mixed>
	 */
	public function check_status_now( $site_id ) {
		$this->calls[] = (int) $site_id;

		return array(
			'category' => 'working',
		);
	}
}

/**
 * Fake event log.
 */
class Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Event_Log {
	/**
	 * Audit calls.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	public $audit_calls = array();

	/**
	 * Records audit.
	 *
	 * @param string              $action Action.
	 * @param string              $outcome Outcome.
	 * @param array<string,mixed> $context Context.
	 * @return void
	 */
	public function audit_action( $action, $outcome, array $context = array() ) {
		$this->audit_calls[] = array(
			'action'  => $action,
			'outcome' => $outcome,
			'context' => $context,
		);
	}
}

/**
 * Fake site repository.
 */
class Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Sites {
	/**
	 * Current site row.
	 *
	 * @var array<string,mixed>|null
	 */
	public $site = array(
		'id'                => 42,
		'enrollment_status' => 'active',
	);

	/**
	 * Pause calls.
	 *
	 * @var array<int,int>
	 */
	public $pause_calls = array();

	/**
	 * Resume calls.
	 *
	 * @var array<int,int>
	 */
	public $resume_calls = array();

	/**
	 * Archive calls.
	 *
	 * @var array<int,int>
	 */
	public $archive_calls = array();

	/**
	 * Unarchive calls.
	 *
	 * @var array<int,int>
	 */
	public $unarchive_calls = array();

	/**
	 * Pause result.
	 *
	 * @var bool
	 */
	public $pause_result = true;

	/**
	 * Resume result.
	 *
	 * @var bool
	 */
	public $resume_result = true;

	/**
	 * Archive result.
	 *
	 * @var bool
	 */
	public $archive_result = true;

	/**
	 * Unarchive result.
	 *
	 * @var bool
	 */
	public $unarchive_result = true;

	/**
	 * Gets a site.
	 *
	 * @param int $site_id Site ID.
	 * @return array<string,mixed>|null
	 */
	public function get( $site_id ) {
		if ( ! is_array( $this->site ) || (int) $this->site['id'] !== (int) $site_id ) {
			return null;
		}

		return $this->site;
	}

	/**
	 * Records pause.
	 *
	 * @param int $site_id Site ID.
	 * @return bool
	 */
	public function pause_polling( $site_id ) {
		$this->pause_calls[] = (int) $site_id;

		return $this->pause_result;
	}

	/**
	 * Records resume.
	 *
	 * @param int $site_id Site ID.
	 * @return bool
	 */
	public function resume_polling( $site_id ) {
		$this->resume_calls[] = (int) $site_id;

		return $this->resume_result;
	}

	/**
	 * Records archive.
	 *
	 * @param int $site_id Site ID.
	 * @return bool
	 */
	public function archive_local( $site_id ) {
		$this->archive_calls[] = (int) $site_id;

		return $this->archive_result;
	}

	/**
	 * Records unarchive.
	 *
	 * @param int $site_id Site ID.
	 * @return bool
	 */
	public function unarchive_local( $site_id ) {
		$this->unarchive_calls[] = (int) $site_id;

		return $this->unarchive_result;
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
