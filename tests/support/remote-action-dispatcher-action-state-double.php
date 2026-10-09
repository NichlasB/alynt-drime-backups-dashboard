<?php
/**
 * Fake action repository state methods for remote action dispatcher tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Request and state recording methods for dispatcher action repository tests.
 */
trait Alynt_Drime_Backups_Dashboard_Test_Dispatcher_Action_State_Double {
	/**
	 * Stores request context.
	 *
	 * @param int                 $site_id Site ID.
	 * @param string              $action_type Action type.
	 * @param int                 $requested_by User ID.
	 * @param string              $idempotency_key Idempotency key.
	 * @param string              $action_key_id Action key ID.
	 * @param string              $expires_at Expiry.
	 * @param string              $request_fingerprint Fingerprint.
	 * @param array<string,mixed> $context Context.
	 * @param string              $public_id Public ID.
	 * @return int
	 */
	public function create_request( $site_id, $action_type, $requested_by, $idempotency_key, $action_key_id, $expires_at, $request_fingerprint = '', array $context = array(), $public_id = '' ) {
		$this->requests[] = compact( 'site_id', 'action_type', 'requested_by', 'idempotency_key', 'action_key_id', 'expires_at', 'request_fingerprint', 'context', 'public_id' );

		return 55;
	}

	/**
	 * Marks dispatch.
	 *
	 * @param int $action_id Action ID.
	 * @return bool
	 */
	public function mark_dispatched( $action_id ) {
		unset( $action_id );
		return true;
	}

	/**
	 * Marks state.
	 *
	 * @param int    $action_id Action ID.
	 * @param string $state State.
	 * @param string $result_code Result code.
	 * @param string $result_summary Summary.
	 * @param int    $retry_after_seconds Retry after.
	 * @return bool
	 */
	public function mark_state( $action_id, $state, $result_code = '', $result_summary = '', $retry_after_seconds = 0 ) {
		$this->latest_state = compact( 'action_id', 'state', 'result_code', 'result_summary', 'retry_after_seconds' );

		return true;
	}
}
