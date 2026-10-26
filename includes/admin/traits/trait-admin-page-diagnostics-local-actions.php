<?php
/**
 * Admin page diagnostics local action handlers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.45
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles dashboard-local Diagnostics POST actions for the admin page.
 *
 * @since 0.1.45
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Diagnostics_Local_Actions {
	/**
	 * Handles diagnostics settings updates.
	 *
	 * @return array<string,mixed>|WP_Error
	 */
	private function handle_update_diagnostics_settings_action() {
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

	/**
	 * Handles clearing retained diagnostics events.
	 *
	 * @return array<string,mixed>|WP_Error
	 */
	private function handle_clear_diagnostics_events_action() {
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
}
