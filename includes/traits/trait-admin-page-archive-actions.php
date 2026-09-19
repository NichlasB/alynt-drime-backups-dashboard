<?php
/**
 * Admin page local archive action handlers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.37
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles dashboard-local archive/unarchive POST actions for the admin page.
 *
 * @since 0.1.37
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Archive_Actions {
	/**
	 * Handles local dashboard record archival.
	 *
	 * @return array<string,mixed>|WP_Error
	 */
	private function handle_archive_local_action() {
		return $this->handle_archive_state_action(
			'archive_local',
			'alynt_drime_backups_dashboard_archive_local',
			true
		);
	}

	/**
	 * Handles local dashboard record unarchival.
	 *
	 * @return array<string,mixed>|WP_Error
	 */
	private function handle_unarchive_local_action() {
		return $this->handle_archive_state_action(
			'unarchive_local',
			'alynt_drime_backups_dashboard_unarchive_local',
			false
		);
	}

	/**
	 * Handles one dashboard-local archive-state transition.
	 *
	 * @param string $action Action result slug.
	 * @param string $nonce_action Nonce action.
	 * @param bool   $archive Whether to archive or unarchive.
	 * @return array<string,mixed>|WP_Error
	 */
	private function handle_archive_state_action( $action, $nonce_action, $archive ) {
		$nonce = $this->verify_action_nonce( $nonce_action );

		if ( is_wp_error( $nonce ) ) {
			return $nonce;
		}

		$site_id = isset( $_POST['dashboard_site_id'] ) ? absint( wp_unslash( $_POST['dashboard_site_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Verified by verify_action_nonce() above.
		$site    = $site_id > 0 ? $this->sites->get( $site_id ) : null;

		if ( empty( $site ) || ! is_array( $site ) ) {
			$result = new WP_Error( 'site_not_found', __( 'The dashboard site record was not found.', 'alynt-drime-backups-dashboard' ) );
		} elseif ( $archive && ! $this->can_archive_local_record( $site ) ) {
			$result = new WP_Error( 'site_archive_not_allowed', __( 'Only revoked records and expired pending records can be archived locally. Revoke active records first; active polling and credentials are not changed by archive controls.', 'alynt-drime-backups-dashboard' ) );
		} else {
			$result = $archive ? $this->sites->archive_local( $site_id ) : $this->sites->unarchive_local( $site_id );
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
	 * Determines whether a dashboard-local record is safe to archive.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return bool
	 */
	private function can_archive_local_record( array $site ) {
		$status = isset( $site['enrollment_status'] ) ? sanitize_key( (string) $site['enrollment_status'] ) : '';

		if ( 'revoked' === $status ) {
			return true;
		}

		if ( 'pending' !== $status ) {
			return false;
		}

		$expires_at = isset( $site['pairing_expires_at'] ) ? (string) $site['pairing_expires_at'] : '';

		if ( '' === $expires_at ) {
			return true;
		}

		$expires = strtotime( $expires_at );

		return false === $expires || $expires <= time();
	}
}
