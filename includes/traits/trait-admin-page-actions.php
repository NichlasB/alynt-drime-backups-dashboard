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

		if ( 'create_pending_site' === $action ) {
			$nonce = $this->verify_action_nonce( 'alynt_drime_backups_dashboard_create_pending_site' );

			if ( is_wp_error( $nonce ) ) {
				return $nonce;
			}

			$pending_site = isset( $_POST['alynt_drime_backups_dashboard_pending_site'] ) ? wp_unslash( $_POST['alynt_drime_backups_dashboard_pending_site'] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Verified by verify_action_nonce() above.
			$raw          = is_array( $pending_site ) ? $pending_site : array();

			$result = $this->enrollment_manager->create_pending_site( $raw, home_url( '/', 'https' ) );

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

		if ( 'revoke_local' === $action ) {
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

		if ( 'check_status_now' === $action ) {
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

		if ( 'generate_action_opt_in_token' === $action ) {
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

		if ( 'request_backup_now' === $action ) {
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

		if ( 'update_diagnostics_settings' === $action ) {
			$nonce = $this->verify_action_nonce( 'alynt_drime_backups_dashboard_update_diagnostics_settings' );

			if ( is_wp_error( $nonce ) ) {
				return $nonce;
			}

			$settings = isset( $_POST['alynt_drime_backups_dashboard_diagnostics'] ) ? wp_unslash( $_POST['alynt_drime_backups_dashboard_diagnostics'] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Verified by verify_action_nonce() above.
			$success  = is_array( $settings ) && $this->event_log->update_settings( $settings );

			$this->record_admin_audit_action(
				'update_diagnostics_settings',
				$success ? 'succeeded' : 'failed',
				array(
					'diagnostics_logging_enabled' => is_array( $settings ) && ! empty( $settings['enabled'] ),
					'minimum_level'               => is_array( $settings ) && isset( $settings['minimum_level'] ) ? sanitize_key( (string) $settings['minimum_level'] ) : '',
				)
			);

			return array(
				'action'  => 'update_diagnostics_settings',
				'success' => $success,
			);
		}

		if ( 'clear_diagnostics_events' === $action ) {
			$nonce = $this->verify_action_nonce( 'alynt_drime_backups_dashboard_clear_diagnostics_events' );

			if ( is_wp_error( $nonce ) ) {
				return $nonce;
			}

			$success = $this->event_log->clear();

			$this->record_admin_audit_action(
				'clear_diagnostics_events',
				$success ? 'succeeded' : 'failed'
			);

			return array(
				'action'  => 'clear_diagnostics_events',
				'success' => $success,
			);
		}

		return new WP_Error( 'dashboard_action_unknown', __( 'The requested dashboard action is not supported.', 'alynt-drime-backups-dashboard' ) );
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

	/**
	 * Renders an action result notice.
	 *
	 * @param array<string,mixed>|WP_Error|null $result Result.
	 * @return void
	 */
	private function render_action_result( $result ) {
		if ( null === $result ) {
			return;
		}

		if ( is_wp_error( $result ) ) {
			$this->render_action_notice( $result->get_error_message(), 'notice-error' );
			return;
		}

		if ( isset( $result['pairing_token'] ) ) {
			$this->render_action_notice( __( 'Pending dashboard site created. Copy the pairing token now; it is not stored and cannot be shown again.', 'alynt-drime-backups-dashboard' ), 'notice-success' );
			return;
		}

		if ( isset( $result['action'] ) && 'revoke_local' === $result['action'] ) {
			$message = ! empty( $result['success'] )
				? __( 'Dashboard record revoked locally. No client site or Drime action was attempted.', 'alynt-drime-backups-dashboard' )
				: __( 'The dashboard record could not be revoked locally. Refresh the site detail screen and try again; the record may already have changed.', 'alynt-drime-backups-dashboard' );
			$class   = ! empty( $result['success'] ) ? 'notice-success' : 'notice-error';

			$this->render_action_notice( $message, $class );
			return;
		}

		if ( isset( $result['action'] ) && 'check_status_now' === $result['action'] ) {
			$this->render_action_notice( __( 'Read-only status check completed and stored. No backup or client-site setting was changed.', 'alynt-drime-backups-dashboard' ), 'notice-success' );
			return;
		}

		if ( isset( $result['action'] ) && 'generate_action_opt_in_token' === $result['action'] ) {
			$this->render_action_notice( __( 'V2 action opt-in token generated. Copy it now; the token is not stored and cannot be shown again.', 'alynt-drime-backups-dashboard' ), 'notice-success' );
			return;
		}

		if ( isset( $result['action'] ) && 'request_backup_now' === $result['action'] ) {
			$remote_state = isset( $result['remote_state'] ) ? sanitize_key( (string) $result['remote_state'] ) : '';

			if ( in_array( $remote_state, array( 'accepted', 'running', 'succeeded' ), true ) ) {
				$message = ! empty( $result['poll_after_dispatch'] )
					? __( 'Request Backup Now was accepted by the client site, and a read-only status check was completed.', 'alynt-drime-backups-dashboard' )
					: __( 'Request Backup Now was accepted by the client site. Wait briefly, then use Check Now to confirm the latest reported result.', 'alynt-drime-backups-dashboard' );
				$this->render_action_notice( $message, 'notice-success' );
				return;
			}

			if ( in_array( $remote_state, array( 'rate_limited', 'busy', 'rejected', 'unsupported' ), true ) ) {
				$this->render_action_notice( isset( $result['result_summary'] ) ? (string) $result['result_summary'] : __( 'The client site did not accept the remote action request.', 'alynt-drime-backups-dashboard' ), 'notice-warning' );
				return;
			}

			$this->render_action_notice( __( 'Request Backup Now could not be completed. Review the remote action history for this site.', 'alynt-drime-backups-dashboard' ), 'notice-error' );
			return;
		}

		if ( isset( $result['action'] ) && 'update_diagnostics_settings' === $result['action'] ) {
			$message = ! empty( $result['success'] )
				? __( 'Diagnostics settings saved.', 'alynt-drime-backups-dashboard' )
				: __( 'Diagnostics settings could not be saved. Refresh the page and try again; if it continues, check that WordPress options can be updated.', 'alynt-drime-backups-dashboard' );
			$class   = ! empty( $result['success'] ) ? 'notice-success' : 'notice-error';

			$this->render_action_notice( $message, $class );
			return;
		}

		if ( isset( $result['action'] ) && 'clear_diagnostics_events' === $result['action'] ) {
			$message = ! empty( $result['success'] )
				? __( 'Diagnostics events cleared.', 'alynt-drime-backups-dashboard' )
				: __( 'Diagnostics events could not be cleared. Refresh the Diagnostics screen and try again; the retained event buffer may already have changed.', 'alynt-drime-backups-dashboard' );
			$class   = ! empty( $result['success'] ) ? 'notice-success' : 'notice-error';

			$this->render_action_notice( $message, $class );
		}
	}

	/**
	 * Renders a submitted-action notice with an explicit live-region role.
	 *
	 * @param string $message Notice message.
	 * @param string $notice_class WordPress notice tone class.
	 * @return void
	 */
	private function render_action_notice( $message, $notice_class ) {
		$is_error = 'notice-error' === $notice_class;

		printf(
			'<div id="adbd-action-notice" class="notice %1$s is-dismissible inline" role="%2$s" aria-live="%3$s"><p>%4$s</p></div>',
			esc_attr( $notice_class ),
			$is_error ? 'alert' : 'status',
			$is_error ? 'assertive' : 'polite',
			esc_html( $message )
		);
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
