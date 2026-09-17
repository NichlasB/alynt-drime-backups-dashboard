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
	 * Gets the latest snapshot for one site.
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
				"SELECT * FROM {$table} WHERE dashboard_site_id = %d ORDER BY observed_at DESC, id DESC LIMIT 1",
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
