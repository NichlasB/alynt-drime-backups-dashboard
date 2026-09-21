<?php
/**
 * Admin page remote action handlers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.26
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles signed remote-action POST requests for the admin page.
 *
 * @since 0.1.26
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Remote_Actions {
	/**
	 * Handles V2 action opt-in token generation.
	 *
	 * @return array<string,mixed>|WP_Error
	 */
	private function handle_generate_action_opt_in_token_action() {
		$nonce = $this->verify_action_nonce( 'alynt_drime_backups_dashboard_generate_action_opt_in_token' );

		if ( is_wp_error( $nonce ) ) {
			return $nonce;
		}

		$site_id = isset( $_POST['dashboard_site_id'] ) ? absint( wp_unslash( $_POST['dashboard_site_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Verified by verify_action_nonce() above.
		$result  = $this->action_opt_in_manager->create_opt_in_token( $site_id, home_url( '/', 'https' ) );

		$this->record_admin_audit_action(
			'generate_action_opt_in_token',
			is_wp_error( $result ) ? 'failed' : 'succeeded',
			array(
				'dashboard_site_id' => $site_id,
				'action_type'       => Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCAN_UPLOAD_NOW,
				'error_code'        => is_wp_error( $result ) ? $result->get_error_code() : '',
			)
		);

		return $result;
	}

	/**
	 * Handles Request Backup Now.
	 *
	 * @return array<string,mixed>|WP_Error
	 */
	private function handle_request_backup_now_action() {
		$nonce = $this->verify_action_nonce( 'alynt_drime_backups_dashboard_request_backup_now' );

		if ( is_wp_error( $nonce ) ) {
			return $nonce;
		}

		$site_id      = isset( $_POST['dashboard_site_id'] ) ? absint( wp_unslash( $_POST['dashboard_site_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Verified by verify_action_nonce() above.
		$requested_by = function_exists( 'get_current_user_id' ) ? absint( get_current_user_id() ) : 0;
		$result       = $this->remote_action_dispatcher->request_scan_upload_now( $site_id, $requested_by );

		$this->record_admin_audit_action(
			'request_backup_now',
			is_wp_error( $result ) ? 'failed' : 'succeeded',
			array(
				'dashboard_site_id' => $site_id,
				'action_type'       => Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCAN_UPLOAD_NOW,
				'remote_state'      => is_array( $result ) && isset( $result['remote_state'] ) ? sanitize_key( (string) $result['remote_state'] ) : '',
				'error_code'        => is_wp_error( $result ) ? $result->get_error_code() : '',
			)
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		if ( $this->remote_action_should_poll_after_dispatch( $result ) ) {
			$poll_result = $site_id > 0 ? $this->poller->check_status_now( $site_id ) : new WP_Error( 'site_not_found', __( 'The dashboard site record was not found.', 'alynt-drime-backups-dashboard' ) );

			$result['poll_after_dispatch'] = ! is_wp_error( $poll_result );
			$result['poll_error_code']     = is_wp_error( $poll_result ) ? $poll_result->get_error_code() : '';
		}

		return $result;
	}

	/**
	 * Handles a non-mutating schedule preview request.
	 *
	 * @return array<string,mixed>|WP_Error
	 */
	private function handle_preview_schedule_change_action() {
		$nonce = $this->verify_action_nonce( 'alynt_drime_backups_dashboard_preview_schedule_change' );

		if ( is_wp_error( $nonce ) ) {
			return $nonce;
		}

		$site_id          = isset( $_POST['dashboard_site_id'] ) ? absint( wp_unslash( $_POST['dashboard_site_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Verified by verify_action_nonce() above.
		$schedule_id      = isset( $_POST['schedule_id'] ) ? sanitize_key( wp_unslash( $_POST['schedule_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Verified by verify_action_nonce() above.
		$proposed_cadence = isset( $_POST['proposed_cadence'] ) ? sanitize_key( wp_unslash( $_POST['proposed_cadence'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Verified by verify_action_nonce() above.
		$requested_by     = function_exists( 'get_current_user_id' ) ? absint( get_current_user_id() ) : 0;
		$result           = $this->remote_action_dispatcher->request_schedule_preview( $site_id, $schedule_id, $proposed_cadence, $requested_by );

		$this->record_admin_audit_action(
			'preview_schedule_change',
			is_wp_error( $result ) ? 'failed' : 'succeeded',
			array(
				'dashboard_site_id' => $site_id,
				'action_type'       => Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCHEDULE_PREVIEW,
				'schedule_id'       => $schedule_id,
				'proposed_cadence'  => $proposed_cadence,
				'remote_state'      => is_array( $result ) && isset( $result['remote_state'] ) ? sanitize_key( (string) $result['remote_state'] ) : '',
				'error_code'        => is_wp_error( $result ) ? $result->get_error_code() : '',
			)
		);

		return $result;
	}

	/**
	 * Handles an approved Schedule Apply request.
	 *
	 * @return array<string,mixed>|WP_Error
	 */
	private function handle_apply_schedule_change_action() {
		$nonce = $this->verify_action_nonce( 'alynt_drime_backups_dashboard_apply_schedule_change' );

		if ( is_wp_error( $nonce ) ) {
			return $nonce;
		}

		$site_id           = isset( $_POST['dashboard_site_id'] ) ? absint( wp_unslash( $_POST['dashboard_site_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Verified by verify_action_nonce() above.
		$preview_action_id = isset( $_POST['preview_action_id'] ) ? sanitize_text_field( wp_unslash( $_POST['preview_action_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Verified by verify_action_nonce() above.

		if ( empty( $_POST['schedule_apply_confirm'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified by verify_action_nonce() above.
			$result = new WP_Error( 'schedule_apply_confirmation_required', __( 'Confirm that Schedule Apply changes only future Alynt uploader scan cadence before applying this preview.', 'alynt-drime-backups-dashboard' ) );
		} else {
			$requested_by = function_exists( 'get_current_user_id' ) ? absint( get_current_user_id() ) : 0;
			$result       = $this->remote_action_dispatcher->request_schedule_apply( $site_id, $preview_action_id, $requested_by );
		}

		$this->record_admin_audit_action(
			'apply_schedule_change',
			is_wp_error( $result ) ? 'failed' : 'succeeded',
			array(
				'dashboard_site_id' => $site_id,
				'action_type'       => Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCHEDULE_APPLY,
				'preview_action_id' => $preview_action_id,
				'remote_state'      => is_array( $result ) && isset( $result['remote_state'] ) ? sanitize_key( (string) $result['remote_state'] ) : '',
				'error_code'        => is_wp_error( $result ) ? $result->get_error_code() : '',
			)
		);

		return $result;
	}

	/**
	 * Handles a non-mutating Schedule Rollback Preview request.
	 *
	 * @since 0.1.43
	 *
	 * @return array<string,mixed>|WP_Error
	 */
	private function handle_preview_schedule_rollback_action() {
		$nonce = $this->verify_action_nonce( 'alynt_drime_backups_dashboard_preview_schedule_rollback' );

		if ( is_wp_error( $nonce ) ) {
			return $nonce;
		}

		$site_id                = isset( $_POST['dashboard_site_id'] ) ? absint( wp_unslash( $_POST['dashboard_site_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Verified by verify_action_nonce() above.
		$source_apply_action_id = isset( $_POST['source_apply_action_id'] ) ? sanitize_text_field( wp_unslash( $_POST['source_apply_action_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Verified by verify_action_nonce() above.

		if ( empty( $_POST['schedule_rollback_preview_confirm'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified by verify_action_nonce() above.
			$result = new WP_Error( 'schedule_rollback_preview_confirmation_required', __( 'Confirm that Schedule Rollback Preview is non-mutating before sending this request.', 'alynt-drime-backups-dashboard' ) );
		} else {
			$requested_by = function_exists( 'get_current_user_id' ) ? absint( get_current_user_id() ) : 0;
			$result       = $this->remote_action_dispatcher->request_schedule_rollback_preview( $site_id, $source_apply_action_id, $requested_by );
		}

		$this->record_admin_audit_action(
			'preview_schedule_rollback',
			is_wp_error( $result ) ? 'failed' : 'succeeded',
			array(
				'dashboard_site_id'      => $site_id,
				'action_type'            => Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCHEDULE_ROLLBACK_PREVIEW,
				'source_apply_action_id' => $source_apply_action_id,
				'remote_state'           => is_array( $result ) && isset( $result['remote_state'] ) ? sanitize_key( (string) $result['remote_state'] ) : '',
				'error_code'             => is_wp_error( $result ) ? $result->get_error_code() : '',
			)
		);

		return $result;
	}

	/**
	 * Returns whether a dispatch response should be followed by a read-only poll.
	 *
	 * @param array<string,mixed> $result Dispatch result.
	 * @return bool
	 */
	private function remote_action_should_poll_after_dispatch( array $result ) {
		$remote_state = isset( $result['remote_state'] ) ? sanitize_key( (string) $result['remote_state'] ) : '';

		return in_array( $remote_state, array( 'accepted', 'running', 'succeeded' ), true );
	}
}
