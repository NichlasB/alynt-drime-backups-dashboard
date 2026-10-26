<?php
/**
 * Admin page Site Detail local removal preview helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.65
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds display-only readiness evidence for future local record removal.
 *
 * @since 0.1.65
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Site_Detail_Local_Removal_Preview {
	/**
	 * Builds a read-only local removal preview for one site.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @param int                 $site_id Site ID.
	 * @return array{eligible:bool,reason:string,snapshot_count:int,action_count:int,non_terminal_action_count:int}
	 */
	private function local_record_removal_preview( array $site, $site_id ) {
		$snapshot_count            = $this->count_repository_rows_for_site( 'snapshots', 'count_for_site', $site_id );
		$action_count              = $this->count_repository_rows_for_site( 'remote_actions', 'count_for_site', $site_id );
		$non_terminal_action_count = $this->count_repository_rows_for_site( 'remote_actions', 'count_non_terminal_for_site', $site_id );
		$reason                    = $this->local_record_removal_blocking_reason( $site, $non_terminal_action_count );

		return array(
			'eligible'                  => '' === $reason,
			'reason'                    => '' === $reason ? __( 'Archived, terminal, and credential-free. A future removal workflow could safely present a separate confirmation gate for this dashboard-local record.', 'alynt-drime-backups-dashboard' ) : $reason,
			'snapshot_count'            => $snapshot_count,
			'action_count'              => $action_count,
			'non_terminal_action_count' => $non_terminal_action_count,
		);
	}

	/**
	 * Gets the first reason an archived record is not ready for a future removal workflow.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @param int                 $non_terminal_action_count Non-terminal remote action rows.
	 * @return string
	 */
	private function local_record_removal_blocking_reason( array $site, $non_terminal_action_count ) {
		$status = isset( $site['enrollment_status'] ) ? sanitize_key( (string) $site['enrollment_status'] ) : '';

		if ( empty( $site['archived_at'] ) ) {
			return __( 'Only archived records can be evaluated for future local removal.', 'alynt-drime-backups-dashboard' );
		}

		if ( 'revoked' !== $status && ! $this->is_expired_pending_local_record( $site ) ) {
			return __( 'Only revoked records and expired pending records can be considered for future local removal.', 'alynt-drime-backups-dashboard' );
		}

		if ( $this->site_has_polling_credentials( $site ) ) {
			return __( 'Polling credentials are still present. Revoke the record before considering future local removal.', 'alynt-drime-backups-dashboard' );
		}

		if ( ! empty( $site['action_key_id'] ) || ! empty( $site['action_private_key_ciphertext'] ) ) {
			return __( 'Remote-action signing credentials are still present. Revocation must clear them before local removal can be considered.', 'alynt-drime-backups-dashboard' );
		}

		if ( ! empty( $site['next_poll_at'] ) ) {
			return __( 'A next poll time is still stored. Future removal should require the record to be fully unscheduled.', 'alynt-drime-backups-dashboard' );
		}

		if ( ! empty( $site['paused_at'] ) ) {
			return __( 'The record is paused rather than fully terminal. Revoke or expire it before local removal can be considered.', 'alynt-drime-backups-dashboard' );
		}

		if ( $non_terminal_action_count > 0 ) {
			return __( 'One or more retained action rows are still non-terminal. Future removal should wait until action history is terminal.', 'alynt-drime-backups-dashboard' );
		}

		return '';
	}

	/**
	 * Determines whether a site is an expired pending local record.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return bool
	 */
	private function is_expired_pending_local_record( array $site ) {
		$status = isset( $site['enrollment_status'] ) ? sanitize_key( (string) $site['enrollment_status'] ) : '';

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

	/**
	 * Counts rows through an optional repository method.
	 *
	 * @param string $property Repository property name.
	 * @param string $method Repository method name.
	 * @param int    $site_id Site ID.
	 * @return int
	 */
	private function count_repository_rows_for_site( $property, $method, $site_id ) {
		if ( ! isset( $this->{$property} ) || ! is_object( $this->{$property} ) || ! method_exists( $this->{$property}, $method ) ) {
			return 0;
		}

		return max( 0, (int) $this->{$property}->{$method}( $site_id ) );
	}
}
