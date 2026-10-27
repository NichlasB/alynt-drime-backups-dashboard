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
 * Handles remote action lookup and recent-history query methods.
 *
 * @since 0.1.26
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Lookups {

	use Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Schedule_Apply_Lookups;

	/**
	 * Finds one dashboard action by public ID and dashboard site.
	 *
	 * @since 0.1.16
	 *
	 * @param string $public_id Public action UUID.
	 * @param int    $site_id Dashboard site ID.
	 * @return array<string,mixed>|null
	 */
	public function find_by_public_id_for_site( $public_id, $site_id ) {
		global $wpdb;

		$public_id = $this->sanitize_uuid( $public_id );
		$site_id   = absint( $site_id );

		if ( '' === $public_id || 0 === $site_id ) {
			return null;
		}

		$table = Alynt_Drime_Backups_Dashboard_Storage::actions_table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Repository reads a plugin-owned custom table; callers own caching decisions.
		$row = $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is produced by Storage for a plugin-owned custom table.
				"SELECT * FROM {$table} WHERE public_id = %s AND dashboard_site_id = %d LIMIT 1",
				$public_id,
				$site_id
			),
			ARRAY_A
		);

		return is_array( $row ) ? $row : null;
	}

	/**
	 * Gets latest action for one site.
	 *
	 * @since 0.1.15
	 *
	 * @param int $site_id Site ID.
	 * @return array<string,mixed>|null
	 */
	public function latest_for_site( $site_id ) {
		global $wpdb;

		$site_id = absint( $site_id );

		if ( 0 === $site_id ) {
			return null;
		}

		$table = Alynt_Drime_Backups_Dashboard_Storage::actions_table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Repository reads a plugin-owned custom table; callers own caching decisions.
		$row = $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is produced by Storage for a plugin-owned custom table.
				"SELECT * FROM {$table} WHERE dashboard_site_id = %d ORDER BY requested_at DESC, id DESC LIMIT 1",
				$site_id
			),
			ARRAY_A
		);

		return is_array( $row ) ? $row : null;
	}

	/**
	 * Gets recent action rows for one site.
	 *
	 * @since 0.1.15
	 *
	 * @param int $site_id Site ID.
	 * @param int $limit Limit.
	 * @return array<int,array<string,mixed>>
	 */
	public function recent_for_site( $site_id, $limit = 10 ) {
		global $wpdb;

		$site_id = absint( $site_id );
		$limit   = max( 1, min( 50, (int) $limit ) );

		if ( 0 === $site_id ) {
			return array();
		}

		$table = Alynt_Drime_Backups_Dashboard_Storage::actions_table();
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is produced by Storage for a plugin-owned custom table.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Repository reads a plugin-owned custom table; callers own caching decisions.
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, public_id, dashboard_site_id, action_type, state, requested_by, requested_at, accepted_at,
				completed_at, last_seen_at, retry_after_seconds, result_code, result_summary,
				client_state, client_result_code, client_result_summary, client_counts_json,
				client_updated_at, reconciled_at, redacted_context_json
				FROM {$table}
				WHERE dashboard_site_id = %d
				ORDER BY requested_at DESC, id DESC
				LIMIT %d",
				$site_id,
				$limit
			),
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Counts remote action rows retained for one site.
	 *
	 * @since 0.1.62
	 *
	 * @param int $site_id Site ID.
	 * @return int
	 */
	public function count_for_site( $site_id ) {
		global $wpdb;

		$site_id = absint( $site_id );

		if ( 0 === $site_id ) {
			return 0;
		}

		$table = Alynt_Drime_Backups_Dashboard_Storage::actions_table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Repository reads a plugin-owned custom table; callers own caching decisions.
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is produced by Storage for a plugin-owned custom table.
				"SELECT COUNT(*) FROM {$table} WHERE dashboard_site_id = %d",
				$site_id
			)
		);
	}

	/**
	 * Counts non-terminal remote action rows retained for one site.
	 *
	 * @since 0.1.62
	 *
	 * @param int $site_id Site ID.
	 * @return int
	 */
	public function count_non_terminal_for_site( $site_id ) {
		global $wpdb;

		$site_id = absint( $site_id );

		if ( 0 === $site_id ) {
			return 0;
		}

		$table = Alynt_Drime_Backups_Dashboard_Storage::actions_table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Repository reads a plugin-owned custom table; callers own caching decisions.
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is produced by Storage for a plugin-owned custom table.
				"SELECT COUNT(*) FROM {$table} WHERE dashboard_site_id = %d AND state IN ('queued_for_dispatch', 'accepted', 'running')",
				$site_id
			)
		);
	}

	/**
	 * Counts retained action rows in batches, keeping each count kind independent.
	 *
	 * Failed grouped reads use the corresponding existing scalar reader once per
	 * affected site. Empty groups are zero, not failed queries requiring fallback.
	 *
	 * @param array<int> $site_ids Site IDs, normalized like the scalar readers.
	 * @param bool       $non_terminal Count only the existing non-terminal states.
	 * @return array<int,int>
	 */
	public function counts_by_site_ids( array $site_ids, $non_terminal = false ) {
		global $wpdb;
		$site_ids = array_values( array_unique( array_filter( array_map( 'absint', $site_ids ) ) ) );
		$counts   = array_fill_keys( $site_ids, 0 );
		if ( empty( $site_ids ) ) {
			return $counts;
		}
		$table = Alynt_Drime_Backups_Dashboard_Storage::actions_table();
		$state = $non_terminal ? " AND state IN ('queued_for_dispatch', 'accepted', 'running')" : '';
		foreach ( array_chunk( $site_ids, 50 ) as $batch ) {
			$placeholders = implode( ',', array_fill( 0, count( $batch ), '%d' ) );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Independent plugin-owned aggregate, no persistent cache.
			$rows = $wpdb->get_results(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Trusted table, fixed state clause and generated integer placeholders; all site values are prepared.
				$wpdb->prepare( "SELECT dashboard_site_id, COUNT(*) AS row_count FROM {$table} WHERE dashboard_site_id IN ({$placeholders}){$state} GROUP BY dashboard_site_id", $batch ),
				ARRAY_A
			);
			if ( ! is_array( $rows ) || ! empty( $wpdb->last_error ) ) {
				foreach ( $batch as $site_id ) {
					$counts[ $site_id ] = max( 0, $non_terminal ? $this->count_non_terminal_for_site( $site_id ) : $this->count_for_site( $site_id ) );
				}
				continue;
			}
			$batch_ids = array_fill_keys( $batch, true );
			foreach ( $rows as $row ) {
				$site_id = (int) $row['dashboard_site_id'];
				if ( isset( $batch_ids[ $site_id ] ) ) {
					$counts[ $site_id ] = max( 0, (int) $row['row_count'] );
				}
			}
		}
		return $counts;
	}

	/**
	 * Reads one action row by internal ID.
	 *
	 * @param int $action_id Action ID.
	 * @return array<string,mixed>|null
	 */
	private function row_by_id( $action_id ) {
		global $wpdb;

		$action_id = absint( $action_id );

		if ( 0 === $action_id ) {
			return null;
		}

		$table = Alynt_Drime_Backups_Dashboard_Storage::actions_table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Repository reads one plugin-owned custom-table row.
		$row = $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is produced by Storage for a plugin-owned custom table.
				"SELECT * FROM {$table} WHERE id = %d LIMIT 1",
				$action_id
			),
			ARRAY_A
		);

		return is_array( $row ) ? $row : null;
	}

	/**
	 * Decodes a redacted context JSON field.
	 *
	 * @param array<string,mixed> $row Action row.
	 * @return array<string,mixed>
	 */
	private function context_from_row( array $row ) {
		$context = ! empty( $row['redacted_context_json'] ) ? json_decode( (string) $row['redacted_context_json'], true, 16 ) : array();

		return is_array( $context ) ? $context : array();
	}
}
