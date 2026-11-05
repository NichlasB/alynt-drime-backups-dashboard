<?php
/**
 * Fixed adapter operation/cleanup ownership, including optional dormant capture.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** No constructors, callbacks, native factories or general dispatch interface. */
trait Alynt_Drime_Backups_Dashboard_Source_Policy_Adapter_Operation {
	/** Operation-local capture, absent from all historical private paths.
	 *
	 * @var object|null
	 */
	private $operation_context = null;

	/**
	 * Fixed dormant handoff, no raw connection, settings or native grant returned.
	 *
	 * @param object $context Privately bound pre-connect capture.
	 * @return true|WP_Error
	 */
	public function initialize_context( $context ) {
		if ( $this->busy || ! $context instanceof Alynt_Drime_Backups_Dashboard_Source_Policy_Trusted_Context
			|| ! $this->connection instanceof Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection ) {
			return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_unavailable' );
		}
		$this->busy = true;
		try {
			return $this->connection->bootstrap_production( $context );
		} finally {
			$this->busy = false;
		}
	}

	/**
	 * Own one operation and release only its connection, including refusals.
	 *
	 * @param string      $operation Internal literal.
	 * @param mixed       $revision Expected revision.
	 * @param mixed       $site_id Site identity.
	 * @param mixed       $source Fixed source.
	 * @param mixed       $mode Desired mode.
	 * @param object|null $context Optional closed capture, never caller authority.
	 * @return array|string|WP_Error
	 * @throws RuntimeException Caught locally when private context changes.
	 */
	private function run( $operation, $revision, $site_id, $source, $mode, $context = null ) {
		if ( $this->busy ) {
			return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_unavailable' );
		}
		$this->busy              = true;
		$this->submitted         = false;
		$this->operation_context = null;
		try {
			if ( null !== $context ) {
				if ( ! $context instanceof Alynt_Drime_Backups_Dashboard_Source_Policy_Trusted_Context || ! $context->valid( $this->connection ) ) {
					throw new RuntimeException( 'Policy capture unavailable' );
				}
				$this->operation_context = $context;
			}
			$result = $this->perform( $operation, $revision, $site_id, $source, $mode );
		} catch ( Throwable $error ) {
			$result = Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( $this->submitted ? 'source_policy_write_uncertain' : 'source_policy_unavailable' );
		}
		try {
			if ( $this->connection instanceof Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection && true !== $this->connection->close() ) {
				$result = Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( $this->submitted ? 'source_policy_write_uncertain' : 'source_policy_unavailable' );
			}
		} catch ( Throwable $error ) {
			$result = Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( $this->submitted ? 'source_policy_write_uncertain' : 'source_policy_unavailable' );
		} finally {
			$this->operation_context = null;
			$this->busy              = false;
		}
		return $result;
	}
}
