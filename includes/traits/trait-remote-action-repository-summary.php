<?php
/**
 * Remote action repository helper trait.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.26
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *
Handles remote action support summaries and retention cleanup.
 *
 * @since 0.1.26
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Summary {


	/**
	 * Builds support-safe aggregate action-history diagnostics.
	 *
	 * @since 0.1.16
	 *
	 * @return array<string,mixed>
	 */
	public function support_summary() {
		global $wpdb;

		if ( ! is_object( $wpdb ) || empty( $wpdb->prefix ) ) {
			return $this->empty_support_summary();
		}

		$table = Alynt_Drime_Backups_Dashboard_Storage::actions_table();
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is produced by Storage for a plugin-owned custom table.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Repository reads aggregate support-safe data from a plugin-owned custom table.
		$row = $wpdb->get_row(
			"SELECT COUNT(*) AS total,
				SUM(CASE WHEN client_state IS NOT NULL AND client_state != '' THEN 1 ELSE 0 END) AS client_reconciled,
				SUM(CASE WHEN state = 'stale' THEN 1 ELSE 0 END) AS stale,
				SUM(CASE WHEN state IN ('accepted', 'running') THEN 1 ELSE 0 END) AS awaiting_confirmation,
				SUM(CASE WHEN action_type = 'schedule_apply' THEN 1 ELSE 0 END) AS schedule_apply,
				SUM(CASE WHEN action_type = 'schedule_rollback_preview' THEN 1 ELSE 0 END) AS schedule_rollback_preview,
				SUM(CASE WHEN action_type = 'schedule_apply' AND redacted_context_json LIKE '%\"rollback_metadata\"%' THEN 1 ELSE 0 END) AS rollback_metadata_captured,
				MAX(updated_at) AS latest_updated_at
			FROM {$table}",
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( ! is_array( $row ) ) {
			$row = array();
		}

		return array(
			'total'                     => isset( $row['total'] ) ? max( 0, (int) $row['total'] ) : 0,
			'client_reconciled'         => isset( $row['client_reconciled'] ) ? max( 0, (int) $row['client_reconciled'] ) : 0,
			'stale'                     => isset( $row['stale'] ) ? max( 0, (int) $row['stale'] ) : 0,
			'awaiting_confirmation'     => isset( $row['awaiting_confirmation'] ) ? max( 0, (int) $row['awaiting_confirmation'] ) : 0,
			'schedule_apply'            => isset( $row['schedule_apply'] ) ? max( 0, (int) $row['schedule_apply'] ) : 0,
			'schedule_rollback_preview' => isset( $row['schedule_rollback_preview'] ) ? max( 0, (int) $row['schedule_rollback_preview'] ) : 0,
			'rollback_metadata'         => isset( $row['rollback_metadata_captured'] ) ? max( 0, (int) $row['rollback_metadata_captured'] ) : 0,
			'latest_updated_at'         => isset( $row['latest_updated_at'] ) ? (string) $row['latest_updated_at'] : '',
		);
	}

	/**
	 * Gets an empty support-safe aggregate action summary.
	 *
	 * @return array<string,mixed>
	 */
	private function empty_support_summary() {
		return array(
			'total'                     => 0,
			'client_reconciled'         => 0,
			'stale'                     => 0,
			'awaiting_confirmation'     => 0,
			'schedule_apply'            => 0,
			'schedule_rollback_preview' => 0,
			'rollback_metadata'         => 0,
			'latest_updated_at'         => '',
		);
	}

	/**
	 * Deletes old completed action records in bounded batches.
	 *
	 * @since 0.1.15
	 *
	 * @param int $retention_days Retention days.
	 * @param int $batch_size Batch size.
	 * @return int|WP_Error
	 */
	public function cleanup_retention( $retention_days = self::RETENTION_DAYS, $batch_size = 500 ) {
		global $wpdb;

		$retention_days = max( 1, min( 365, (int) $retention_days ) );
		$batch_size     = max( 1, min( 5000, (int) $batch_size ) );
		$cutoff         = gmdate( 'Y-m-d H:i:s', time() - ( $retention_days * 86400 ) );
		$table          = Alynt_Drime_Backups_Dashboard_Storage::actions_table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Repository deletes from a plugin-owned custom table in bounded batches.
		$deleted = $wpdb->query(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is produced by Storage for a plugin-owned custom table.
				"DELETE FROM {$table}
				WHERE completed_at IS NOT NULL
					AND completed_at < %s
				ORDER BY completed_at ASC, id ASC
				LIMIT %d",
				$cutoff,
				$batch_size
			)
		);

		if ( false === $deleted ) {
			return new WP_Error( 'remote_action_cleanup_failed', __( 'The dashboard could not clean up old remote action records.', 'alynt-drime-backups-dashboard' ) );
		}

		return (int) $deleted;
	}
}
