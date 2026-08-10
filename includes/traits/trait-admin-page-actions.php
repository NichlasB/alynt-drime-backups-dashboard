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

			return $this->enrollment_manager->create_pending_site( $raw, home_url( '/', 'https' ) );
		}

		if ( 'revoke_local' === $action ) {
			$nonce = $this->verify_action_nonce( 'alynt_drime_backups_dashboard_revoke_local' );

			if ( is_wp_error( $nonce ) ) {
				return $nonce;
			}

			$site_id = isset( $_POST['dashboard_site_id'] ) ? absint( wp_unslash( $_POST['dashboard_site_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Verified by verify_action_nonce() above.

			return array(
				'action'  => 'revoke_local',
				'success' => $site_id > 0 && $this->sites->revoke_local( $site_id ),
			);
		}

		if ( 'check_status_now' === $action ) {
			$nonce = $this->verify_action_nonce( 'alynt_drime_backups_dashboard_check_status_now' );

			if ( is_wp_error( $nonce ) ) {
				return $nonce;
			}

			$site_id = isset( $_POST['dashboard_site_id'] ) ? absint( wp_unslash( $_POST['dashboard_site_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Verified by verify_action_nonce() above.
			$result  = $site_id > 0 ? $this->poller->check_status_now( $site_id ) : new WP_Error( 'site_not_found', __( 'The dashboard site record was not found.', 'alynt-drime-backups-dashboard' ) );

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

		if ( 'update_diagnostics_settings' === $action ) {
			$nonce = $this->verify_action_nonce( 'alynt_drime_backups_dashboard_update_diagnostics_settings' );

			if ( is_wp_error( $nonce ) ) {
				return $nonce;
			}

			$settings = isset( $_POST['alynt_drime_backups_dashboard_diagnostics'] ) ? wp_unslash( $_POST['alynt_drime_backups_dashboard_diagnostics'] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Verified by verify_action_nonce() above.

			return array(
				'action'  => 'update_diagnostics_settings',
				'success' => is_array( $settings ) && $this->event_log->update_settings( $settings ),
			);
		}

		if ( 'clear_diagnostics_events' === $action ) {
			$nonce = $this->verify_action_nonce( 'alynt_drime_backups_dashboard_clear_diagnostics_events' );

			if ( is_wp_error( $nonce ) ) {
				return $nonce;
			}

			return array(
				'action'  => 'clear_diagnostics_events',
				'success' => $this->event_log->clear(),
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
			echo '<div class="notice notice-error inline"><p>' . esc_html( $result->get_error_message() ) . '</p></div>';
			return;
		}

		if ( isset( $result['pairing_token'] ) ) {
			echo '<div class="notice notice-success inline"><p>' . esc_html__( 'Pending dashboard site created. Copy the pairing token now; it is not stored and cannot be shown again.', 'alynt-drime-backups-dashboard' ) . '</p></div>';
			return;
		}

		if ( isset( $result['action'] ) && 'revoke_local' === $result['action'] ) {
			$message = ! empty( $result['success'] )
				? __( 'Dashboard record revoked locally. No client site or Drime action was attempted.', 'alynt-drime-backups-dashboard' )
				: __( 'The dashboard record could not be revoked locally.', 'alynt-drime-backups-dashboard' );
			$class   = ! empty( $result['success'] ) ? 'notice-success' : 'notice-error';

			echo '<div class="notice ' . esc_attr( $class ) . ' inline"><p>' . esc_html( $message ) . '</p></div>';
			return;
		}

		if ( isset( $result['action'] ) && 'check_status_now' === $result['action'] ) {
			echo '<div class="notice notice-success inline"><p>' . esc_html__( 'Read-only status check completed and stored.', 'alynt-drime-backups-dashboard' ) . '</p></div>';
			return;
		}

		if ( isset( $result['action'] ) && 'update_diagnostics_settings' === $result['action'] ) {
			$message = ! empty( $result['success'] )
				? __( 'Diagnostics settings saved.', 'alynt-drime-backups-dashboard' )
				: __( 'Diagnostics settings could not be saved.', 'alynt-drime-backups-dashboard' );
			$class   = ! empty( $result['success'] ) ? 'notice-success' : 'notice-error';

			echo '<div class="notice ' . esc_attr( $class ) . ' inline"><p>' . esc_html( $message ) . '</p></div>';
			return;
		}

		if ( isset( $result['action'] ) && 'clear_diagnostics_events' === $result['action'] ) {
			$message = ! empty( $result['success'] )
				? __( 'Diagnostics events cleared.', 'alynt-drime-backups-dashboard' )
				: __( 'Diagnostics events could not be cleared.', 'alynt-drime-backups-dashboard' );
			$class   = ! empty( $result['success'] ) ? 'notice-success' : 'notice-error';

			echo '<div class="notice ' . esc_attr( $class ) . ' inline"><p>' . esc_html( $message ) . '</p></div>';
		}
	}
}
