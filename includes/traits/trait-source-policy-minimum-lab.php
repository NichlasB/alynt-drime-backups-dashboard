<?php
/**
 * Closed lab-only receiver selection, never production provisioning.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Retained scope is composed privately by the fixed proof, null in applications. */
trait Alynt_Drime_Backups_Dashboard_Source_Policy_Minimum_Lab {
	/** Never accepted by a public factory/request.
	 *
	 * @var object|null
	 */
	private $minimum_lab_scope = null;

	/**
	 * Consistency DATA only; no selector argument, scope/handle export or effects.
	 *
	 * @return array|WP_Error Lab expectation or fixed refusal.
	 */
	final public function minimum_lab_profile() {
		$bad = Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_unavailable' );
		if ( 'Alynt_Drime_Backups_Dashboard_Source_Policy_No_Reconnect_Connection' !== get_class( $this ) || $this->close_attempted
			|| ! $this->minimum_lab_scope instanceof Alynt_Drime_Backups_Dashboard_Source_Policy_Minimum_Lab_Scope
			|| ! $this->query_context instanceof Alynt_Drime_Backups_Dashboard_Source_Policy_Query_Context
			|| ! $this->query_context->owns( $this ) || ! in_array( $this->report_mode, array( null, 0 ), true ) ) {
			return $bad;
		}
		$expected = $this->minimum_lab_scope->expectation( $this, $this->query_context );
		if ( false === $expected || ( null !== $this->report_mode && 0 !== $this->observed_report_mode() ) ) {
			return $bad;
		}
		if ( 'active' === $this->native_state ) {
			if ( ! $this->ready || ! is_array( $this->qualification ) || ! $this->query_context->valid() ) {
				return $bad;
			}
			foreach ( array( 'core', 'server', 'database', 'table' ) as $key ) {
				if ( ( $this->qualification[ $key ] ?? null ) !== $expected[ $key ] ) {
					return $bad;
				}
			}
			return $expected;
		}
		if ( $this->ready || ! in_array( $this->native_state, array( 'never_opened', 'allocating', 'allocated', 'connecting', 'connected_unqualified' ), true )
			|| ! is_array( $this->bootstrap_frame ) || ! $this->minimum_lab_scope->frame_matches( $this->bootstrap_frame )
			|| ( 'never_opened' !== $this->native_state && ! $this->query_context->valid() ) ) {
			return $bad;
		}
		return $expected;
	}

	/**
	 * Selects the exact build only for a retained lab binding.
	 *
	 * @param string $core Actual private core.
	 * @return string|false
	 */
	private function bootstrap_expected_server( $core ) {
		if ( null === $this->minimum_lab_scope ) {
			return Alynt_Drime_Backups_Dashboard_Source_Policy_Transport_Profile::server( $core );
		}
		$expected = $this->minimum_lab_profile();
		return is_array( $expected ) && $expected['core'] === $core ? $expected['server'] : false;
	}

	/**
	 * Carries the receiver through final and per-query admission.
	 *
	 * @param array  $facts Actual facts.
	 * @param string $table Actual table.
	 * @return array|WP_Error
	 */
	private function admit_bound_profile( $facts, $table ) {
		return null === $this->minimum_lab_scope
			? Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Profile::admit( $facts, $table, $this->query_context )
			: Alynt_Drime_Backups_Dashboard_Source_Policy_Native_Profile::admit( $facts, $table, $this->query_context, $this );
	}
}
