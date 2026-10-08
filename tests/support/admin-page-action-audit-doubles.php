<?php
/**
 * Admin page action audit test doubles.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

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
