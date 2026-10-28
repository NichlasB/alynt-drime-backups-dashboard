<?php
/**
 * Hook-silent source-policy error DATA; never an authorization or storage grant.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Own error projection/classification only; external core observers stay intact. */
final class Alynt_Drime_Backups_Dashboard_Source_Policy_Error {
	/**
	 * Creates a genuine empty WP_Error, then fills its public error DATA directly.
	 *
	 * No add()/add_data(), translation/filter call, reflection or global hook edit.
	 * The empty core constructor does not dispatch wp_error_added. A later external
	 * is_wp_error() call still has its normal WordPress observer semantics.
	 *
	 * @param mixed $code Fixed policy outcome code, never a grant.
	 * @param bool  $diagnostic Whether to retain the fixed internal diagnostic text.
	 * @return WP_Error
	 * @throws RuntimeException If the required already-loaded core error class is absent.
	 */
	public static function refusal( $code = 'source_policy_unavailable', $diagnostic = false ) {
		$allowed = array( 'source_policy_unavailable', 'source_policy_invalid', 'source_policy_forbidden', 'source_policy_conflict', 'source_policy_storage_error', 'source_policy_write_uncertain', 'source_policy_save_failed' );
		if ( ! is_string( $code ) || ! in_array( $code, $allowed, true ) ) {
			$code = 'source_policy_unavailable';
		}
		if ( ! class_exists( 'WP_Error', false ) ) {
			throw new RuntimeException( 'Policy error class unavailable' );
		}
		$error = new WP_Error();
		// Internal diagnostic DATA, not UI output. UI/handler translation remains at its existing boundary.
		$error->errors[ $code ] = array( true === $diagnostic ? 'The monitoring policy could not be saved safely.' : '' );
		return $error;
	}

	/**
	 * Matches core's type predicate without dispatching is_wp_error_instance.
	 *
	 * @param mixed $value Explicit result DATA; no method/magic/callable probe.
	 * @return bool
	 */
	public static function is_error( $value ) {
		return $value instanceof WP_Error;
	}
}
