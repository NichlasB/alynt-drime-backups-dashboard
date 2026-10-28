<?php
/**
 * Manual audit result-shape checks only; no site access or native eligibility.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Pure manual-proof evidence checks, not a production or authenticated grant. */
final class Alynt_Drime_Backups_Dashboard_Bootstrap_Error_Evidence {
	/**
	 * Checks the fixed result contract for one audited operation.
	 *
	 * @param string $group Fixed manual case name.
	 * @param mixed  $result Observed result, never an owner or handle.
	 * @return bool
	 */
	public static function result_matches( $group, $result ) {
		if ( 'core-classification' === $group ) {
			return true === $result;
		}
		if ( 'public-query' === $group ) {
			return false === $result;
		}
		if ( ! in_array( $group, array( 'core-construction', 'factory', 'admission', 'private-bootstrap' ), true ) || ! $result instanceof WP_Error || 'WP_Error' !== get_class( $result ) ) {
			return false;
		}
		// Reject malformed public DATA before calling the pinned core's getters.
		if ( array( 'source_policy_unavailable' => array( '' ) ) !== $result->errors || array() !== $result->error_data ) {
			return false;
		}
		return 'source_policy_unavailable' === $result->get_error_code() && '' === $result->get_error_message() && null === $result->get_error_data();
	}
}
