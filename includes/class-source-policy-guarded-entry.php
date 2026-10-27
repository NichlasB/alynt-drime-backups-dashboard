<?php
/**
 * Dormant request/view/result contract, not a registered endpoint or live facade.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Private collaborators have no production factory in this foundation. */
final class Alynt_Drime_Backups_Dashboard_Source_Policy_Guarded_Entry {
	/**
	 * Fixed owner, provisioned only by isolated fixtures here.
	 *
	 * @var object|null
	 */
	private $owner = null;
	/**
	 * Existing site repository contract, never a request callback.
	 *
	 * @var object|null
	 */
	private $sites = null;

	/** Opens/constructs nothing. */
	private function __construct() {}

	/**
	 * Leaves production provisioning closed without inspecting authentication/state.
	 *
	 * @return WP_Error
	 */
	public static function create() {
		return new WP_Error( 'source_policy_unavailable' );
	}

	/**
	 * Requires current capability and record resolution before one owner view read.
	 *
	 * @param mixed $site_id Raw site identity.
	 * @param mixed $source Fixed allowed source.
	 * @return array|WP_Error
	 */
	public function view( $site_id, $source ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'source_policy_forbidden' );
		}
		if ( ( ! is_string( $site_id ) && ! is_int( $site_id ) ) || 'wpvivid' !== $source ) {
			return new WP_Error( 'source_policy_invalid' );
		}
		$site_id = absint( $site_id );
		if ( $site_id <= 0 || ! $this->sites || ! $this->owner || ! $this->record_matches( $site_id ) ) {
			return new WP_Error( 'source_policy_unavailable' );
		}
		$view = $this->owner->view_source_mode( $site_id, 'wpvivid' );
		if ( is_wp_error( $view ) ) {
			return $this->safe_error( $view );
		}
		if ( ! is_array( $view ) || array( 'mode', 'revision' ) !== array_keys( $view ) || ! in_array( $view['mode'], array( 'required', 'external_optional' ), true ) || ! $this->revision_valid( $view['revision'] ) ) {
			return new WP_Error( 'source_policy_unavailable' );
		}
		return $view;
	}

	/**
	 * Admits raw POST-like DATA in capability/nonce/type/revision/record order.
	 *
	 * @param mixed $request Explicit request DATA; no global POST is read here.
	 * @return string|WP_Error Literal saved/unchanged, never model arrays/truthiness.
	 */
	public function submit( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'source_policy_forbidden' );
		}
		if ( ! is_array( $request ) || ! isset( $request['_wpnonce'] ) || ! is_string( $request['_wpnonce'] ) || strlen( $request['_wpnonce'] ) > 128 || ! wp_verify_nonce( wp_unslash( $request['_wpnonce'] ), 'alynt_drime_backups_dashboard_update_source_policy' ) ) {
			return new WP_Error( 'source_policy_invalid' );
		}
		if ( ! isset( $request['dashboard_site_id'], $request['source_key'], $request['source_mode'], $request['source_policy_revision'] ) || ( ! is_string( $request['dashboard_site_id'] ) && ! is_int( $request['dashboard_site_id'] ) ) || ! is_string( $request['source_key'] ) || ! is_string( $request['source_mode'] ) || ! is_string( $request['source_policy_revision'] ) ) {
			return new WP_Error( 'source_policy_invalid' );
		}
		$site_id  = absint( wp_unslash( $request['dashboard_site_id'] ) );
		$source   = wp_unslash( $request['source_key'] );
		$mode     = wp_unslash( $request['source_mode'] );
		$revision = wp_unslash( $request['source_policy_revision'] );
		if ( $site_id <= 0 || 'wpvivid' !== $source || ! in_array( $mode, array( 'required', 'external_optional' ), true ) || ! $this->revision_valid( $revision ) ) {
			return new WP_Error( 'source_policy_invalid' );
		}
		if ( ! $this->sites || ! $this->owner || ! $this->record_matches( $site_id ) ) {
			return new WP_Error( 'source_policy_unavailable' );
		}
		$result = $this->owner->save_source_mode_if_current( $revision, $site_id, $source, $mode );
		if ( is_wp_error( $result ) ) {
			return $this->safe_error( $result );
		}
		return in_array( $result, array( 'saved', 'unchanged' ), true ) ? $result : new WP_Error( 'source_policy_save_failed' );
	}

	/**
	 * Requires the resolved record to name the admitted site exactly.
	 *
	 * @param int $site_id Admitted site identity.
	 * @return bool
	 */
	private function record_matches( $site_id ) {
		$record = $this->sites->get( $site_id );
		return is_array( $record ) && isset( $record['id'] ) && ( is_string( $record['id'] ) || is_int( $record['id'] ) ) && (string) $record['id'] === (string) $site_id;
	}

	/**
	 * Does not coerce or normalize malformed consistency DATA.
	 *
	 * @param mixed $revision Expected digest.
	 * @return bool
	 */
	private function revision_valid( $revision ) {
		return is_string( $revision ) && 1 === preg_match( '/\A[a-f0-9]{64}\z/', $revision );
	}

	/**
	 * Projects codes only; collaborator messages/data/handles never escape.
	 *
	 * @param WP_Error $error Collaborator error.
	 * @return WP_Error
	 */
	private function safe_error( WP_Error $error ) {
		$code = $error->get_error_code();
		return new WP_Error( in_array( $code, array( 'source_policy_conflict', 'source_policy_unavailable', 'source_policy_invalid', 'source_policy_storage_error', 'source_policy_write_uncertain' ), true ) ? $code : 'source_policy_save_failed' );
	}
}
