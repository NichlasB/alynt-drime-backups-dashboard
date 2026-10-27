<?php
/**
 * Request-ID rejection and non-vacuous admission through actual handler traits.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/support/admin-page-actions-test-harness.php';
require_once __DIR__ . '/support/admin-request-id-tripwire.php';

class AdminPageCompoundRequestIdTest extends TestCase {
	use Alynt_Drime_Backups_Dashboard_Admin_Action_Test_Case;

	private function guarded_harness( $allow_entry = false ) {
		$harness = $this->admin_action_harness();
		$tripwire = new Alynt_Drime_Backups_Dashboard_Test_Request_ID_Tripwire( $allow_entry );
		foreach ( array( 'sites', 'source_policy', 'action_opt_in_manager', 'remote_action_dispatcher', 'poller', 'event_log', 'enrollment_manager' ) as $property ) {
			$harness->{$property} = $tripwire;
		}
		return $harness;
	}

	private function request( $action, $id ) {
		$_POST = array(
			'alynt_drime_backups_dashboard_action' => $action,
			'_wpnonce' => 'valid', 'dashboard_site_id' => $id,
			'source_key' => 'wpvivid', 'source_mode' => 'required',
			'schedule_id' => 'alynt_scan', 'proposed_cadence' => 'hourly',
			'preview_action_id' => 'owned-preview', 'source_apply_action_id' => 'owned-apply',
			'schedule_apply_confirm' => '1', 'schedule_rollback_preview_confirm' => '1',
		);
	}

	/** @dataProvider rejected_ids */
	public function test_compound_and_non_form_ids_stop_before_every_collaborator( $action, $id ) {
		$this->set_valid_nonce( 'alynt_drime_backups_dashboard_' . $action );
		$this->request( $action, $id );
		$result = $this->guarded_harness()->handle_for_test();
		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'site_not_found', $result->get_error_code() );
	}

	/** @dataProvider accepted_ids */
	public function test_valid_ids_still_reach_the_original_first_delegate( $action, $id, $method ) {
		$this->set_valid_nonce( 'alynt_drime_backups_dashboard_' . $action );
		$this->request( $action, $id );
		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'delegate:' . $method . ':42' );
		$this->guarded_harness( true )->handle_for_test();
	}

	/** @dataProvider invalid_nonces */
	public function test_nonce_rejection_precedes_id_rejection( $action, $state ) {
		$this->set_valid_nonce( 'alynt_drime_backups_dashboard_' . $action );
		$this->request( $action, array( '42' ) );
		if ( 'missing' === $state ) unset( $_POST['_wpnonce'] );
		elseif ( 'expired' === $state ) $_POST['_wpnonce'] = 'expired';
		else $this->set_valid_nonce( 'alynt_drime_backups_dashboard_' . ( 'revoke_local' === $action ? 'check_status_now' : 'revoke_local' ) );
		$result = $this->guarded_harness()->handle_for_test();
		$this->assertInstanceOf( WP_Error::class, $result );
		$this->assertSame( 'dashboard_session_expired', $result->get_error_code() );
	}

	/** Non-site forms retain their existing contract even with an unused ID field. */
	public function test_pending_creation_does_not_gain_an_unrelated_site_id_requirement() {
		$this->set_valid_nonce( 'alynt_drime_backups_dashboard_create_pending_site' );
		$this->request( 'create_pending_site', array( 'unused' ) );
		$harness = $this->admin_action_harness();
		$result = $harness->handle_for_test();
		$this->assertSame( 123, $result['site_id'] );
		$this->assertCount( 1, $harness->enrollment_manager->calls );
	}

	public static function routes() {
		return array(
			'revoke_local' => 'revoke_local', 'pause_polling' => 'get', 'resume_polling' => 'get',
			'check_status_now' => 'check_status_now', 'update_source_policy' => 'set_source_mode',
			'archive_local' => 'get', 'unarchive_local' => 'get',
			'generate_action_opt_in_token' => 'create_opt_in_token',
			'request_backup_now' => 'request_scan_upload_now', 'cleanup_preview' => 'request_cleanup_preview',
			'preview_schedule_change' => 'request_schedule_preview',
			'apply_schedule_change' => 'request_schedule_apply',
			'preview_schedule_rollback' => 'request_schedule_rollback_preview',
		);
	}

	public static function rejected_ids() {
		$cases = array();
		foreach ( self::routes() as $action => $method ) {
			foreach ( array( array(), array( '42' ), array( 'nested' => array( '42' ) ), true, 42.5, new stdClass() ) as $index => $id ) {
				$cases[ $action . ':' . $index ] = array( $action, $id );
			}
		}
		return $cases;
	}

	public static function accepted_ids() {
		$cases = array();
		foreach ( self::routes() as $action => $method ) {
			foreach ( array( '42', 42, '0042' ) as $index => $id ) {
				$cases[ $action . ':' . $index ] = array( $action, $id, $method );
			}
		}
		return $cases;
	}

	public static function invalid_nonces() {
		$cases = array();
		foreach ( self::routes() as $action => $method ) {
			foreach ( array( 'missing', 'expired', 'other-domain' ) as $state ) {
				$cases[ $action . ':' . $state ] = array( $action, $state );
			}
		}
		return $cases;
	}
}
