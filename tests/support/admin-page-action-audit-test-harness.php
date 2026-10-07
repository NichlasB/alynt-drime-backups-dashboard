<?php
/**
 * Admin page action audit test harness.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/admin-page-action-audit-wordpress-shims.php';
require_once dirname( __DIR__, 2 ) . '/includes/traits/trait-admin-page-actions.php';

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
