<?php
/**
 * Admin page action audit tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-action-audit-test-harness.php';

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
