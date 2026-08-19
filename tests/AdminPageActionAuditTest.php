<?php
/**
 * Admin page action audit tests.
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
 * Fake enrollment manager for audit tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Action_Audit_Enrollment_Manager {
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
	 * Creates a pending site.
	 *
	 * @param array  $raw Raw pending site data.
	 * @param string $dashboard_origin Dashboard origin.
	 * @return array<string,mixed>|WP_Error
	 */
	public function create_pending_site( array $raw, $dashboard_origin ) {
		unset( $raw, $dashboard_origin );

		return $this->result;
	}
}

/**
 * Fake event log for audit tests.
 */
class Alynt_Drime_Backups_Dashboard_Test_Action_Audit_Event_Log {
	/**
	 * Audit calls.
	 *
	 * @var array<int,array<string,mixed>>
	 */
	public $audits = array();

	/**
	 * Records an audit call.
	 *
	 * @param string              $action Action.
	 * @param string              $outcome Outcome.
	 * @param array<string,mixed> $context Context.
	 * @return bool
	 */
	public function audit_action( $action, $outcome, array $context = array() ) {
		$this->audits[] = array(
			'action'  => $action,
			'outcome' => $outcome,
			'context' => $context,
		);

		return true;
	}
}

/**
 * Harness exposing the private trait action handler.
 */
class Alynt_Drime_Backups_Dashboard_Test_Action_Audit_Harness {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Actions;

	/**
	 * Enrollment manager.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Test_Action_Audit_Enrollment_Manager
	 */
	public $enrollment_manager;

	/**
	 * Event log.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Test_Action_Audit_Event_Log
	 */
	public $event_log;

	/**
	 * Constructor.
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Test_Action_Audit_Enrollment_Manager $enrollment_manager Enrollment manager.
	 * @param Alynt_Drime_Backups_Dashboard_Test_Action_Audit_Event_Log          $event_log Event log.
	 */
	public function __construct( $enrollment_manager, $event_log ) {
		$this->enrollment_manager = $enrollment_manager;
		$this->event_log          = $event_log;
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
 * Tests admin action audit behavior.
 */
class AdminPageActionAuditTest extends TestCase {
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

		$alynt_drime_backups_dashboard_test_nonce_action = 'alynt_drime_backups_dashboard_create_pending_site';
		$alynt_drime_backups_dashboard_test_nonce_value  = 'valid';
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
	 * Valid create-site posts record a local audit event without the token.
	 *
	 * @return void
	 */
	public function test_valid_create_pending_nonce_records_audit_without_pairing_token() {
		$manager   = new Alynt_Drime_Backups_Dashboard_Test_Action_Audit_Enrollment_Manager();
		$event_log = new Alynt_Drime_Backups_Dashboard_Test_Action_Audit_Event_Log();
		$harness   = new Alynt_Drime_Backups_Dashboard_Test_Action_Audit_Harness( $manager, $event_log );

		$_POST = array(
			'alynt_drime_backups_dashboard_action'       => 'create_pending_site',
			'_wpnonce'                                  => 'valid',
			'alynt_drime_backups_dashboard_pending_site' => array(
				'site_label'      => 'Client Site',
				'expected_origin' => 'https://client.example.com',
				'environment'     => 'production',
			),
		);

		$result  = $harness->handle_for_test();
		$encoded = wp_json_encode( $event_log->audits );

		$this->assertIsArray( $result );
		$this->assertCount( 1, $event_log->audits );
		$this->assertSame( 'create_pending_site', $event_log->audits[0]['action'] );
		$this->assertSame( 'succeeded', $event_log->audits[0]['outcome'] );
		$this->assertSame( 123, $event_log->audits[0]['context']['dashboard_site_id'] );
		$this->assertSame( 'production', $event_log->audits[0]['context']['environment'] );
		$this->assertStringNotContainsString( 'adb1.test', $encoded );
		$this->assertStringNotContainsString( 'client.example.com', $encoded );
	}
}
