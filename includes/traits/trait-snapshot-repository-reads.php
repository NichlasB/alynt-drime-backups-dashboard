<?php
/**
 * Dashboard snapshot repository read helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reads redacted status snapshots.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Snapshot_Repository_Reads {
	/**
	 * Gets the highest-ID snapshot, matching fleet reads and retention protection.
	 *
	 * @since 0.1.0
	 *
	 * @param int $site_id Site ID.
	 * @return array<string,mixed>|null
	 */
	public function latest_for_site( $site_id ) {
		global $wpdb;

		$table = Alynt_Drime_Backups_Dashboard_Storage::snapshots_table();
		$row   = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE dashboard_site_id = %d ORDER BY id DESC LIMIT 1",
				(int) $site_id
			),
			ARRAY_A
		);

		if ( ! $row ) {
			return null;
		}

		$row['decoded_payload'] = $this->decode_payload( $row['payload_json'] );

		return $row;
	}

	/**
	 * Gets latest snapshots keyed by site ID.
	 *
	 * @since 0.1.0
	 *
	 * @param array<int> $site_ids Site IDs.
	 * @return array<int,array<string,mixed>>
	 */
	public function latest_by_site_ids( array $site_ids ) {
		global $wpdb;

		$site_ids = array_values( array_filter( array_map( 'absint', $site_ids ) ) );

		if ( empty( $site_ids ) ) {
			return array();
		}

		$table        = Alynt_Drime_Backups_Dashboard_Storage::snapshots_table();
		$placeholders = implode( ',', array_fill( 0, count( $site_ids ), '%d' ) );
		$rows         = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT s1.* FROM {$table} s1
				INNER JOIN (
					SELECT dashboard_site_id, MAX(id) AS latest_id
					FROM {$table}
					WHERE dashboard_site_id IN ({$placeholders})
					GROUP BY dashboard_site_id
				) latest ON latest.latest_id = s1.id",
				$site_ids
			),
			ARRAY_A
		);

		$snapshots = array();

		foreach ( $rows as $row ) {
			$row['decoded_payload']                       = $this->decode_payload( $row['payload_json'] );
			$snapshots[ (int) $row['dashboard_site_id'] ] = $row;
		}

		return $snapshots;
	}

	/**
	 * Gets a bounded recent snapshot history for one site.
	 *
	 * @since 0.1.0
	 *
	 * @param int $site_id Site ID.
	 * @param int $limit Maximum snapshots to return.
	 * @return array<int,array<string,mixed>>
	 */
	public function recent_for_site( $site_id, $limit = 10 ) {
		global $wpdb;

		$site_id = absint( $site_id );
		$limit   = max( 1, min( 50, (int) $limit ) );

		if ( 0 === $site_id ) {
			return array();
		}

		$table = Alynt_Drime_Backups_Dashboard_Storage::snapshots_table();
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, dashboard_site_id, schema_version, observed_at, overall_status, queue_count, uploaded_count, failed_count, active_upload, warning_count, cron_status
				FROM {$table}
				WHERE dashboard_site_id = %d
				ORDER BY observed_at DESC, id DESC
				LIMIT %d",
				$site_id,
				$limit
			),
			ARRAY_A
		);

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Gets independent chronological histories in batches of at most 50 sites.
	 *
	 * Each UNION ALL branch has its own ordering and limit; the final ordering
	 * also preserves chronology because UNION does not preserve branch order.
	 * Only summary columns are read. A failed batch leaves its histories empty,
	 * like failed single-site reads, without N+1 retries or persistent caching.
	 *
	 * @param array<int> $site_ids Site IDs.
	 * @param int        $limit Maximum snapshots per site, clamped to 1..50.
	 * @return array<int,array<int,array<string,mixed>>>
	 */
	public function recent_by_site_ids( array $site_ids, $limit = 10 ) {
		global $wpdb;

		$site_ids  = array_values( array_unique( array_filter( array_map( 'absint', $site_ids ) ) ) );
		$limit     = max( 1, min( 50, (int) $limit ) );
		$histories = array_fill_keys( $site_ids, array() );

		if ( empty( $site_ids ) ) {
			return $histories;
		}

		$table = Alynt_Drime_Backups_Dashboard_Storage::snapshots_table();

		foreach ( array_chunk( $site_ids, 50 ) as $batch ) {
			$batch_ids = array_fill_keys( $batch, true );
			$selects   = array();
			$params    = array();

			foreach ( $batch as $site_id ) {
				$selects[] = "SELECT id, dashboard_site_id, schema_version, observed_at, overall_status, queue_count, uploaded_count, failed_count, active_upload, warning_count, cron_status
					FROM {$table}
					WHERE dashboard_site_id = %d
					ORDER BY observed_at DESC, id DESC
					LIMIT %d";
				$params[]  = $site_id;
				$params[]  = $limit;
			}

			$query = count( $selects ) === 1 ? $selects[0] : '(' . implode( ') UNION ALL (', $selects ) . ') ORDER BY dashboard_site_id ASC, observed_at DESC, id DESC';
			$rows  = $wpdb->get_results( $wpdb->prepare( $query, $params ), ARRAY_A );

			if ( ! is_array( $rows ) ) {
				continue;
			}

			foreach ( $rows as $row ) {
				$site_id = (int) $row['dashboard_site_id'];

				if ( isset( $batch_ids[ $site_id ] ) ) {
					$histories[ $site_id ][] = $row;
				}
			}
		}

		return $histories;
	}

	/**
	 * Counts snapshots for one site.
	 *
	 * @since 0.1.0
	 *
	 * @param int $site_id Site ID.
	 * @return int
	 */
	public function count_for_site( $site_id ) {
		global $wpdb;

		$table = Alynt_Drime_Backups_Dashboard_Storage::snapshots_table();

		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE dashboard_site_id = %d",
				(int) $site_id
			)
		);
	}

	/**
	 * Counts all retained rows in bounded site batches, never history windows.
	 *
	 * Signed/zero integer IDs match count_for_site(). A failed grouped read uses
	 * that existing scalar reader once per affected ID, preserving its behavior.
	 *
	 * @param array<int> $site_ids Site IDs.
	 * @return array<int,int>
	 */
	public function counts_by_site_ids( array $site_ids ) {
		global $wpdb;
		$site_ids = array_values( array_unique( array_map( 'intval', $site_ids ) ) );
		$counts   = array_fill_keys( $site_ids, 0 );
		if ( empty( $site_ids ) ) {
			return $counts;
		}
		$table = Alynt_Drime_Backups_Dashboard_Storage::snapshots_table();
		foreach ( array_chunk( $site_ids, 50 ) as $batch ) {
			$placeholders = implode( ',', array_fill( 0, count( $batch ), '%d' ) );
			$rows         = $wpdb->get_results(
				$wpdb->prepare( "SELECT dashboard_site_id, COUNT(*) AS row_count FROM {$table} WHERE dashboard_site_id IN ({$placeholders}) GROUP BY dashboard_site_id", $batch ),
				ARRAY_A
			);
			if ( ! is_array( $rows ) || ! empty( $wpdb->last_error ) ) {
				foreach ( $batch as $site_id ) {
					$counts[ $site_id ] = max( 0, $this->count_for_site( $site_id ) );
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
	 * Decodes snapshot JSON safely.
	 *
	 * @param string $payload Payload JSON.
	 * @return array<string,mixed>
	 */
	private function decode_payload( $payload ) {
		$decoded = json_decode( (string) $payload, true );

		return is_array( $decoded ) ? $decoded : array();
	}
}
