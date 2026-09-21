<?php
/**
 * Admin page action handling.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles local dashboard actions and action notices for the admin page.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Actions {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Local_Actions;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Archive_Actions;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Remote_Actions;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Action_Notices;

	/**
	 * Handles approved local dashboard POST actions.
	 *
	 * @return array<string,mixed>|WP_Error|null
	 */
	private function handle_post_action() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- The action name selects the nonce action; action-specific verification happens before action payloads are processed.
		if ( empty( $_POST['alynt_drime_backups_dashboard_action'] ) ) {
			return null;
		}

		$action = sanitize_key( wp_unslash( $_POST['alynt_drime_backups_dashboard_action'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Action-specific verification happens before action payloads are processed.

		switch ( $action ) {
			case 'create_pending_site':
				return $this->handle_create_pending_site_action();

			case 'revoke_local':
				return $this->handle_revoke_local_action();

			case 'archive_local':
				return $this->handle_archive_local_action();

			case 'unarchive_local':
				return $this->handle_unarchive_local_action();

			case 'pause_polling':
				return $this->handle_pause_polling_action();

			case 'resume_polling':
				return $this->handle_resume_polling_action();

			case 'check_status_now':
				return $this->handle_check_status_now_action();

			case 'generate_action_opt_in_token':
				return $this->handle_generate_action_opt_in_token_action();

			case 'request_backup_now':
				return $this->handle_request_backup_now_action();

			case 'preview_schedule_change':
				return $this->handle_preview_schedule_change_action();

			case 'apply_schedule_change':
				return $this->handle_apply_schedule_change_action();

			case 'preview_schedule_rollback':
				return $this->handle_preview_schedule_rollback_action();

			case 'update_source_policy':
				return $this->handle_update_source_policy_action();

			case 'update_diagnostics_settings':
				return $this->handle_update_diagnostics_settings_action();

			case 'clear_diagnostics_events':
				return $this->handle_clear_diagnostics_events_action();

			default:
				return new WP_Error( 'dashboard_action_unknown', __( 'The requested dashboard action is not supported.', 'alynt-drime-backups-dashboard' ) );
		}
	}

	/**
	 * Verifies an admin form nonce without wp_die() so the dashboard can render a recovery notice.
	 *
	 * @param string $action Nonce action.
	 * @return true|WP_Error
	 */
	private function verify_action_nonce( $action ) {
		if ( empty( $_POST['_wpnonce'] ) || ! function_exists( 'wp_verify_nonce' ) ) {
			return new WP_Error( 'dashboard_session_expired', __( 'Your dashboard session has expired. Refresh the page and try again.', 'alynt-drime-backups-dashboard' ) );
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) );

		if ( ! wp_verify_nonce( $nonce, $action ) ) {
			return new WP_Error( 'dashboard_session_expired', __( 'Your dashboard session has expired. Refresh the page and try again.', 'alynt-drime-backups-dashboard' ) );
		}

		return true;
	}

	/**
	 * Records a support-safe local operator action audit event when available.
	 *
	 * @param string              $action Action identifier.
	 * @param string              $outcome Action outcome.
	 * @param array<string,mixed> $context Safe context.
	 * @return void
	 */
	private function record_admin_audit_action( $action, $outcome, array $context = array() ) {
		if ( ! isset( $this->event_log ) || ! is_object( $this->event_log ) || ! method_exists( $this->event_log, 'audit_action' ) ) {
			return;
		}

		$this->event_log->audit_action( $action, $outcome, $context );
	}
}
