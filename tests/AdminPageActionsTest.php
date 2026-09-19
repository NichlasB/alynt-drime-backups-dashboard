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
	 * Valid pause posts update only dashboard-local polling state and audit the action.
	 *
	 * @return void
	 */
	public function test_valid_pause_polling_nonce_delegates_to_local_site_repository() {
		global $alynt_drime_backups_dashboard_test_nonce_action;
		global $alynt_drime_backups_dashboard_test_nonce_value;

		$alynt_drime_backups_dashboard_test_nonce_action = 'alynt_drime_backups_dashboard_pause_polling';
		$alynt_drime_backups_dashboard_test_nonce_value  = 'valid';

		$manager = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Enrollment_Manager();
		$harness = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Harness( $manager );

		$_POST = array(
			'alynt_drime_backups_dashboard_action' => 'pause_polling',
			'_wpnonce'                            => 'valid',
			'dashboard_site_id'                   => '42',
		);

		$result = $harness->handle_for_test();

		$this->assertIsArray( $result );
		$this->assertSame( 'pause_polling', $result['action'] );
		$this->assertTrue( $result['success'] );
		$this->assertSame( array( 42 ), $harness->sites->pause_calls );
		$this->assertSame( array(), $harness->sites->resume_calls );
		$this->assertSame( 'pause_polling', $harness->event_log->audit_calls[0]['action'] );
		$this->assertSame( 'succeeded', $harness->event_log->audit_calls[0]['outcome'] );
	}

	/**
	 * Valid resume posts update only dashboard-local polling state and audit the action.
	 *
	 * @return void
	 */
	public function test_valid_resume_polling_nonce_delegates_to_local_site_repository() {
		global $alynt_drime_backups_dashboard_test_nonce_action;
		global $alynt_drime_backups_dashboard_test_nonce_value;

		$alynt_drime_backups_dashboard_test_nonce_action = 'alynt_drime_backups_dashboard_resume_polling';
		$alynt_drime_backups_dashboard_test_nonce_value  = 'valid';

		$manager = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Enrollment_Manager();
		$harness = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Harness( $manager );

		$_POST = array(
			'alynt_drime_backups_dashboard_action' => 'resume_polling',
			'_wpnonce'                            => 'valid',
			'dashboard_site_id'                   => '42',
		);

		$result = $harness->handle_for_test();

		$this->assertIsArray( $result );
		$this->assertSame( 'resume_polling', $result['action'] );
		$this->assertTrue( $result['success'] );
		$this->assertSame( array(), $harness->sites->pause_calls );
		$this->assertSame( array( 42 ), $harness->sites->resume_calls );
		$this->assertSame( 'resume_polling', $harness->event_log->audit_calls[0]['action'] );
		$this->assertSame( 'succeeded', $harness->event_log->audit_calls[0]['outcome'] );
	}

	/**
	 * Revoked dashboard records cannot be paused.
	 *
	 * @return void
	 */
	public function test_pause_polling_rejects_revoked_site_records() {
		global $alynt_drime_backups_dashboard_test_nonce_action;
		global $alynt_drime_backups_dashboard_test_nonce_value;

		$alynt_drime_backups_dashboard_test_nonce_action = 'alynt_drime_backups_dashboard_pause_polling';
		$alynt_drime_backups_dashboard_test_nonce_value  = 'valid';

		$manager = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Enrollment_Manager();
		$harness = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Harness( $manager );
		$harness->sites->site['enrollment_status'] = 'revoked';

		$_POST = array(
			'alynt_drime_backups_dashboard_action' => 'pause_polling',
			'_wpnonce'                            => 'valid',
			'dashboard_site_id'                   => '42',
		);

		$result = $harness->handle_for_test();

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'site_revoked', $result->get_error_code() );
		$this->assertSame( array(), $harness->sites->pause_calls );
		$this->assertSame( 'failed', $harness->event_log->audit_calls[0]['outcome'] );
	}

	/**
	 * Revoked records can be archived locally without contacting client sites.
	 *
	 * @return void
	 */
	public function test_archive_local_allows_revoked_records() {
		global $alynt_drime_backups_dashboard_test_nonce_action;
		global $alynt_drime_backups_dashboard_test_nonce_value;

		$alynt_drime_backups_dashboard_test_nonce_action = 'alynt_drime_backups_dashboard_archive_local';
		$alynt_drime_backups_dashboard_test_nonce_value  = 'valid';

		$manager = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Enrollment_Manager();
		$harness = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Harness( $manager );
		$harness->sites->site['enrollment_status'] = 'revoked';

		$_POST = array(
			'alynt_drime_backups_dashboard_action' => 'archive_local',
			'_wpnonce'                            => 'valid',
			'dashboard_site_id'                   => '42',
		);

		$result = $harness->handle_for_test();

		$this->assertIsArray( $result );
		$this->assertSame( 'archive_local', $result['action'] );
		$this->assertTrue( $result['success'] );
		$this->assertSame( array( 42 ), $harness->sites->archive_calls );
		$this->assertSame( 'archive_local', $harness->event_log->audit_calls[0]['action'] );
		$this->assertSame( 'succeeded', $harness->event_log->audit_calls[0]['outcome'] );
	}

	/**
	 * Active enrolled records cannot be hidden with archive controls.
	 *
	 * @return void
	 */
	public function test_archive_local_rejects_active_records() {
		global $alynt_drime_backups_dashboard_test_nonce_action;
		global $alynt_drime_backups_dashboard_test_nonce_value;

		$alynt_drime_backups_dashboard_test_nonce_action = 'alynt_drime_backups_dashboard_archive_local';
		$alynt_drime_backups_dashboard_test_nonce_value  = 'valid';

		$manager = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Enrollment_Manager();
		$harness = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Harness( $manager );

		$_POST = array(
			'alynt_drime_backups_dashboard_action' => 'archive_local',
			'_wpnonce'                            => 'valid',
			'dashboard_site_id'                   => '42',
		);

		$result = $harness->handle_for_test();

		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'site_archive_not_allowed', $result->get_error_code() );
		$this->assertSame( array(), $harness->sites->archive_calls );
		$this->assertSame( 'failed', $harness->event_log->audit_calls[0]['outcome'] );
	}

	/**
	 * Archived records can be unarchived locally without restoring credentials.
	 *
	 * @return void
	 */
	public function test_unarchive_local_delegates_to_repository() {
		global $alynt_drime_backups_dashboard_test_nonce_action;
		global $alynt_drime_backups_dashboard_test_nonce_value;

		$alynt_drime_backups_dashboard_test_nonce_action = 'alynt_drime_backups_dashboard_unarchive_local';
		$alynt_drime_backups_dashboard_test_nonce_value  = 'valid';

		$manager = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Enrollment_Manager();
		$harness = new Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Harness( $manager );
		$harness->sites->site['enrollment_status'] = 'revoked';
		$harness->sites->site['archived_at']        = '2026-09-19 18:30:00';

		$_POST = array(
			'alynt_drime_backups_dashboard_action' => 'unarchive_local',
			'_wpnonce'                            => 'valid',
			'dashboard_site_id'                   => '42',
		);

		$result = $harness->handle_for_test();

		$this->assertIsArray( $result );
		$this->assertSame( 'unarchive_local', $result['action'] );
		$this->assertTrue( $result['success'] );
		$this->assertSame( array( 42 ), $harness->sites->unarchive_calls );
		$this->assertSame( 'unarchive_local', $harness->event_log->audit_calls[0]['action'] );
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
