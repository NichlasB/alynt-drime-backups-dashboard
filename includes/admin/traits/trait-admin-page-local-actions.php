<?php
/**
 * Admin page local action handlers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.26
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles dashboard-local POST actions for the admin page.
 *
 * @since 0.1.26
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Local_Actions {
	/**
	 * Handles pending-site creation.
	 *
	 * @return array<string,mixed>|WP_Error
	 */
	private function handle_create_pending_site_action() {
		$nonce = $this->verify_action_nonce( 'alynt_drime_backups_dashboard_create_pending_site' );

		if ( is_wp_error( $nonce ) ) {
			return $nonce;
		}

		$pending_site = isset( $_POST['alynt_drime_backups_dashboard_pending_site'] ) ? wp_unslash( $_POST['alynt_drime_backups_dashboard_pending_site'] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Verified by verify_action_nonce() above.
		$raw          = is_array( $pending_site ) ? $pending_site : array();
		$result       = $this->enrollment_manager->create_pending_site( $raw, home_url( '/', 'https' ) );

		$this->record_admin_audit_action(
			'create_pending_site',
			is_wp_error( $result ) ? 'failed' : 'succeeded',
			array(
				'dashboard_site_id' => is_array( $result ) && isset( $result['site_id'] ) ? (int) $result['site_id'] : 0,
				'environment'       => isset( $raw['environment'] ) ? sanitize_key( (string) $raw['environment'] ) : '',
				'error_code'        => is_wp_error( $result ) ? $result->get_error_code() : '',
			)
		);

		return $result;
	}

	/**
	 * Handles local dashboard record revocation.
	 *
	 * @return array<string,mixed>|WP_Error
	 */
	private function handle_revoke_local_action() {
		$nonce = $this->verify_action_nonce( 'alynt_drime_backups_dashboard_revoke_local' );

		if ( is_wp_error( $nonce ) ) {
			return $nonce;
		}

		$site_id = isset( $_POST['dashboard_site_id'] ) ? absint( wp_unslash( $_POST['dashboard_site_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Verified by verify_action_nonce() above.
		$success = $site_id > 0 && $this->sites->revoke_local( $site_id );

		$this->record_admin_audit_action(
			'revoke_local',
			$success ? 'succeeded' : 'failed',
			array(
				'dashboard_site_id' => $site_id,
			)
		);

		return array(
			'action'  => 'revoke_local',
			'success' => $success,
		);
	}

	/**
	 * Handles dashboard-local scheduled polling pause.
	 *
	 * @return array<string,mixed>|WP_Error
	 */
	private function handle_pause_polling_action() {
		return $this->handle_polling_pause_state_action(
			'pause_polling',
			'alynt_drime_backups_dashboard_pause_polling',
			true
		);
	}

	/**
	 * Handles dashboard-local scheduled polling resume.
	 *
	 * @return array<string,mixed>|WP_Error
	 */
	private function handle_resume_polling_action() {
		return $this->handle_polling_pause_state_action(
			'resume_polling',
			'alynt_drime_backups_dashboard_resume_polling',
			false
		);
	}

	/**
	 * Handles one dashboard-local scheduled polling pause-state transition.
	 *
	 * @param string $action Action result slug.
	 * @param string $nonce_action Nonce action.
	 * @param bool   $pause Whether to pause or resume.
	 * @return array<string,mixed>|WP_Error
	 */
	private function handle_polling_pause_state_action( $action, $nonce_action, $pause ) {
		$nonce = $this->verify_action_nonce( $nonce_action );

		if ( is_wp_error( $nonce ) ) {
			return $nonce;
		}

		$site_id = isset( $_POST['dashboard_site_id'] ) ? absint( wp_unslash( $_POST['dashboard_site_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Verified by verify_action_nonce() above.
		$site    = $site_id > 0 ? $this->sites->get( $site_id ) : null;

		if ( empty( $site ) || ! is_array( $site ) ) {
			$result = new WP_Error( 'site_not_found', __( 'The dashboard site record was not found.', 'alynt-drime-backups-dashboard' ) );
		} elseif ( isset( $site['enrollment_status'] ) && 'revoked' === $site['enrollment_status'] ) {
			$result = new WP_Error( 'site_revoked', __( 'Revoked dashboard records cannot be paused or resumed. Re-enroll this site before changing polling state.', 'alynt-drime-backups-dashboard' ) );
		} else {
			$result = $pause ? $this->sites->pause_polling( $site_id ) : $this->sites->resume_polling( $site_id );
		}

		$this->record_admin_audit_action(
			$action,
			is_wp_error( $result ) || ! $result ? 'failed' : 'succeeded',
			array(
				'dashboard_site_id' => $site_id,
				'error_code'        => is_wp_error( $result ) ? $result->get_error_code() : '',
			)
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return array(
			'action'  => $action,
			'success' => (bool) $result,
		);
	}

	/**
	 * Handles a manual read-only status check.
	 *
	 * @return array<string,mixed>|WP_Error
	 */
	private function handle_check_status_now_action() {
		$nonce = $this->verify_action_nonce( 'alynt_drime_backups_dashboard_check_status_now' );

		if ( is_wp_error( $nonce ) ) {
			return $nonce;
		}

		$site_id = isset( $_POST['dashboard_site_id'] ) ? absint( wp_unslash( $_POST['dashboard_site_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Verified by verify_action_nonce() above.
		$result  = $site_id > 0 ? $this->poller->check_status_now( $site_id ) : new WP_Error( 'site_not_found', __( 'The dashboard site record was not found.', 'alynt-drime-backups-dashboard' ) );

		$this->record_admin_audit_action(
			'check_status_now',
			is_wp_error( $result ) ? 'failed' : 'succeeded',
			array(
				'dashboard_site_id' => $site_id,
				'status_category'   => is_array( $result ) && isset( $result['category'] ) ? sanitize_key( (string) $result['category'] ) : '',
				'error_code'        => is_wp_error( $result ) ? $result->get_error_code() : '',
			)
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return array_merge(
			$result,
			array(
				'action' => 'check_status_now',
			)
		);
	}

	/**
	 * Handles dashboard-local backup source monitoring policy updates.
	 *
	 * @return array<string,mixed>|WP_Error
	 */
	private function handle_update_source_policy_action() {
		$nonce = $this->verify_action_nonce( 'alynt_drime_backups_dashboard_update_source_policy' );

		if ( is_wp_error( $nonce ) ) {
			return $nonce;
		}

		$site_id    = isset( $_POST['dashboard_site_id'] ) ? absint( wp_unslash( $_POST['dashboard_site_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Verified by verify_action_nonce() above.
		$source_key = isset( $_POST['source_key'] ) ? sanitize_key( wp_unslash( $_POST['source_key'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Verified by verify_action_nonce() above.
		$mode       = isset( $_POST['source_mode'] ) ? sanitize_key( wp_unslash( $_POST['source_mode'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Verified by verify_action_nonce() above.
		$success    = $site_id > 0 && $this->source_policy->set_source_mode( $site_id, $source_key, $mode );

		$this->record_admin_audit_action(
			'update_source_policy',
			$success ? 'succeeded' : 'failed',
			array(
				'dashboard_site_id' => $site_id,
				'source_key'        => $source_key,
				'source_mode'       => $mode,
			)
		);

		return array(
			'action'     => 'update_source_policy',
			'success'    => $success,
			'source_key' => $source_key,
			'mode'       => $mode,
		);
	}
}
