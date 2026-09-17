<?php
/**
 * Dashboard snapshot repository retention helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cleans up retained status snapshots.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Snapshot_Repository_Retention {
	/**
	 * Deletes old snapshots while preserving the latest snapshot for every site.
	 *
	 * @since 0.1.0
	 *
	 * @param int $retention_days Days to retain.
	 * @param int $batch_size Maximum rows to delete in one run.
	 * @return int|WP_Error Deleted row count, or an error when cleanup fails.
	 */
	public function cleanup_retention( $retention_days = 30, $batch_size = 500 ) {
		global $wpdb;

		$retention_days = max( 1, min( 365, (int) $retention_days ) );
		$batch_size     = max( 1, min( 5000, (int) $batch_size ) );
		$cutoff         = gmdate( 'Y-m-d H:i:s', time() - ( $retention_days * 86400 ) );
		$table          = Alynt_Drime_Backups_Dashboard_Storage::snapshots_table();

		$deleted = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$table}
				WHERE observed_at < %s
					AND id NOT IN (
						SELECT latest_id FROM (
							SELECT MAX(id) AS latest_id
							FROM {$table}
							GROUP BY dashboard_site_id
						) latest_snapshots
					)
				ORDER BY observed_at ASC, id ASC
				LIMIT %d",
				$cutoff,
				$batch_size
			)
		);

		if ( false === $deleted ) {
			return new WP_Error(
				'snapshot_cleanup_failed',
				__( 'The dashboard could not clean up old status snapshots.', 'alynt-drime-backups-dashboard' ),
				array(
					'last_error' => isset( $wpdb->last_error ) ? sanitize_text_field( $wpdb->last_error ) : '',
				)
			);
		}

		return (int) $deleted;
	}
}
