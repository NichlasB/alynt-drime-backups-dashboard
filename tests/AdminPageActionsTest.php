<?php
/**
 * Admin page action tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

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

require_once dirname( __DIR__ ) . '/includes/traits/trait-admin-page-actions.php';

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
	 * Constructor.
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Enrollment_Manager $enrollment_manager Enrollment manager.
	 */
	public function __construct( $enrollment_manager ) {
		$this->enrollment_manager       = $enrollment_manager;
		$this->remote_action_dispatcher = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Dispatcher();
		$this->poller                   = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Poller();
		$this->event_log                = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Event_Log();
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

/**
 * Tests admin action behavior.
 */
class AdminPageActionsTest extends TestCase {
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

		$alynt_drime_backups_dashboard_test_nonce_action = '';
		$alynt_drime_backups_dashboard_test_nonce_value  = '';
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
	 * Invalid form nonces return a recoverable error and do not process payloads.
	 *
	 * @return void
	 */
	public function test_expired_create_pending_nonce_returns_recovery_error_without_delegating() {
		$manager = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Enrollment_Manager();
		$harness = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Harness( $manager );

		$_POST = array(
			'alynt_drime_backups_dashboard_action'       => 'create_pending_site',
			'_wpnonce'                                  => 'expired',
			'alynt_drime_backups_dashboard_pending_site' => array(
				'expected_origin' => 'https://client.example.com',
			),
		);

		$result = $harness->handle_for_test();

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'dashboard_session_expired', $result->get_error_code() );
		$this->assertSame( array(), $manager->calls );
	}

	/**
	 * Valid create-site posts delegate only after the action-specific nonce passes.
	 *
	 * @return void
	 */
	public function test_valid_create_pending_nonce_delegates_payload_and_dashboard_origin() {
		global $alynt_drime_backups_dashboard_test_nonce_action;
		global $alynt_drime_backups_dashboard_test_nonce_value;

		$alynt_drime_backups_dashboard_test_nonce_action = 'alynt_drime_backups_dashboard_create_pending_site';
		$alynt_drime_backups_dashboard_test_nonce_value  = 'valid';

		$manager = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Enrollment_Manager();
		$harness = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Harness( $manager );

		$_POST = array(
			'alynt_drime_backups_dashboard_action'       => 'create_pending_site',
			'_wpnonce'                                  => 'valid',
			'alynt_drime_backups_dashboard_pending_site' => array(
				'site_label'      => 'Client Site',
				'expected_origin' => 'https://client.example.com',
				'environment'     => 'staging',
			),
		);

		$result = $harness->handle_for_test();

		$this->assertIsArray( $result );
		$this->assertSame( 'adb1.test', $result['pairing_token'] );
		$this->assertCount( 1, $manager->calls );
		$this->assertSame( $_POST['alynt_drime_backups_dashboard_pending_site'], $manager->calls[0]['raw'] );
		$this->assertSame( 'https://control.sitesmanage.com/', $manager->calls[0]['dashboard_origin'] );
	}

	/**
	 * Request Backup Now delegates after nonce validation and performs a read-only follow-up poll.
	 *
	 * @return void
	 */
	public function test_valid_request_backup_now_nonce_delegates_and_polls_after_acceptance() {
		global $alynt_drime_backups_dashboard_test_nonce_action;
		global $alynt_drime_backups_dashboard_test_nonce_value;

		$alynt_drime_backups_dashboard_test_nonce_action = 'alynt_drime_backups_dashboard_request_backup_now';
		$alynt_drime_backups_dashboard_test_nonce_value  = 'valid';

		$manager = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Enrollment_Manager();
		$harness = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Harness( $manager );

		$_POST = array(
			'alynt_drime_backups_dashboard_action' => 'request_backup_now',
			'_wpnonce'                            => 'valid',
			'dashboard_site_id'                   => '42',
		);

		$result = $harness->handle_for_test();

		$this->assertIsArray( $result );
		$this->assertSame( 'request_backup_now', $result['action'] );
		$this->assertTrue( $result['poll_after_dispatch'] );
		$this->assertSame( array( array( 'site_id' => 42, 'requested_by' => 77 ) ), $harness->remote_action_dispatcher->calls );
		$this->assertSame( array( 42 ), $harness->poller->calls );
		$this->assertSame( 'request_backup_now', $harness->event_log->audit_calls[0]['action'] );
	}

	/**
	 * Unsupported actions remain local errors.
	 *
	 * @return void
	 */
	public function test_unknown_action_returns_error_without_nonce_check() {
		$manager = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Enrollment_Manager();
		$harness = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Harness( $manager );

		$_POST = array(
			'alynt_drime_backups_dashboard_action' => 'remote_restore',
		);

		$result = $harness->handle_for_test();

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'dashboard_action_unknown', $result->get_error_code() );
		$this->assertSame( array(), $manager->calls );
	}
}
