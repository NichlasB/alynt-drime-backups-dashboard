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
	 * Constructor.
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Test_Admin_Action_Enrollment_Manager $enrollment_manager Enrollment manager.
	 */
	public function __construct( $enrollment_manager ) {
		$this->enrollment_manager = $enrollment_manager;
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

		$this->previous_post = $_POST;
		$_POST              = array();

		$alynt_drime_backups_dashboard_test_nonce_action = '';
		$alynt_drime_backups_dashboard_test_nonce_value  = '';
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
