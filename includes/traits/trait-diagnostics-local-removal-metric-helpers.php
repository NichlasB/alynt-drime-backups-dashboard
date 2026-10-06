<?php
/**
 * Diagnostics local-removal metric helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.63
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides local-removal readiness helpers for diagnostics metrics.
 *
 * @since 0.1.63
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Local_Removal_Metric_Helpers {
	/**
	 * Builds support-safe local removal-readiness diagnostics for an archived record.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return array<string,int>
	 */
	private function local_removal_readiness_diagnostics( array $site ) {
		$site_id                   = isset( $site['id'] ) ? (int) $site['id'] : 0;
		$snapshot_count            = $this->count_repository_rows_for_site( 'snapshots', 'count_for_site', $site_id );
		$action_count              = $this->count_repository_rows_for_site( 'remote_actions', 'count_for_site', $site_id );
		$non_terminal_action_count = $this->count_repository_rows_for_site( 'remote_actions', 'count_non_terminal_for_site', $site_id );
		$is_ready                  = $this->local_removal_readiness_blocking_reason( $site, $non_terminal_action_count ) === '';

		return array(
			'archived_records'         => 1,
			'ready_records'            => $is_ready ? 1 : 0,
			'blocked_records'          => $is_ready ? 0 : 1,
			'retained_snapshot_rows'   => $snapshot_count,
			'retained_action_rows'     => $action_count,
			'non_terminal_action_rows' => $non_terminal_action_count,
		);
	}

	/**
	 * Gets the first support-safe reason an archived record is not removal-ready.
	 *
	 * This intentionally mirrors the Site Detail preview boundary without exposing
	 * row-level labels, domains, credentials, paths, payloads, or raw action data.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @param int                 $non_terminal_action_count Non-terminal action rows.
	 * @return string
	 */
	private function local_removal_readiness_blocking_reason( array $site, $non_terminal_action_count ) {
		$status = isset( $site['enrollment_status'] ) ? sanitize_key( (string) $site['enrollment_status'] ) : '';

		if ( empty( $site['archived_at'] ) ) {
			return 'not_archived';
		}

		if ( 'revoked' !== $status && ! $this->is_expired_pending_local_record( $site ) ) {
			return 'not_terminal_status';
		}

		if ( $this->has_polling_credentials( $site ) ) {
			return 'polling_credentials_present';
		}

		if ( ! empty( $site['action_key_id'] ) || ! empty( $site['action_private_key_ciphertext'] ) ) {
			return 'action_credentials_present';
		}

		if ( ! empty( $site['next_poll_at'] ) ) {
			return 'scheduled_poll_present';
		}

		if ( ! empty( $site['paused_at'] ) ) {
			return 'paused_not_terminal';
		}

		if ( $non_terminal_action_count > 0 ) {
			return 'non_terminal_action_history';
		}

		return '';
	}

	/**
	 * Determines whether a pending local record has expired pairing state.
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
	 * Counts plugin-owned rows through a repository when the method is available.
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

		$repository_class = get_class( $this->{$property} );

		$has_database = isset( $GLOBALS['wpdb'] ) && is_object( $GLOBALS['wpdb'] );
		$is_test_repo = false !== strpos( $repository_class, '_Test_' );

		if ( ! $has_database && ! $is_test_repo ) {
			return 0;
		}

		return max( 0, (int) $this->{$property}->{$method}( $site_id ) );
	}
}
