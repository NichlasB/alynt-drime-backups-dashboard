<?php
/**
 * Fixed opaque-context handoff to the existing private initializer, never a mint.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Public factories stay closed; ordinary callers cannot construct the context. */
trait Alynt_Drime_Backups_Dashboard_Source_Policy_Production_Boundary {
	/** Same operation-local context from capture to close.
	 *
	 * @var object|null
	 */
	private $production_context = null;

	/**
	 * Existing private settings must already match; no setter or frame is exposed.
	 *
	 * @param object $context Fixed privately composed capture.
	 * @return true|WP_Error Never a receiver, handle or descriptor.
	 */
	final public function bootstrap_production( $context ) {
		if ( $this->bootstrap_attempted || $this->close_attempted || $this->ready || null !== $this->dbh || null !== $this->production_context
			|| null !== $this->minimum_lab_scope || ! $context instanceof Alynt_Drime_Backups_Dashboard_Source_Policy_Trusted_Context
			|| ! $context->bootstrap_matches( $this, $this->query_context, $this->bootstrap_frame, $this->bootstrap_binding ) ) {
			return Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_unavailable' );
		}
		$this->production_context = $context;
		return $this->bootstrap();
	}

	/** Actual private initialization completion, never supplied ready/facts DATA.
	 *
	 * @param object $context Exact retained context.
	 * @param object $query Exact retained query context.
	 * @return bool
	 */
	final public function production_completed( $context, $query ) {
		return $this->production_context === $context && null !== $context && null === $this->minimum_lab_scope
			&& $this->query_context === $query
			&& $this->bootstrap_attempted && ! $this->close_attempted && 8 === $this->bootstrap_position && $this->bootstrap_credits <= 16
			&& 'active' === $this->native_state && true === $this->ready && null !== $this->bootstrap_handle && $this->bootstrap_handle === $this->dbh;
	}

	/** Bound expectation DATA only; two simultaneous origins always refuse.
	 *
	 * @return array|WP_Error
	 */
	final public function bound_profile() {
		$bad = Alynt_Drime_Backups_Dashboard_Source_Policy_Error::refusal( 'source_policy_unavailable' );
		if ( null === $this->production_context ) {
			return $this->minimum_lab_profile();
		}
		if ( null !== $this->minimum_lab_scope || $this->close_attempted
			|| ! $this->production_context instanceof Alynt_Drime_Backups_Dashboard_Source_Policy_Trusted_Context ) {
			return $bad;
		}
		$expected = $this->production_context->production_expectation( $this, $this->query_context );
		return is_array( $expected ) ? $expected : $bad;
	}
}
