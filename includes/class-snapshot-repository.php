<?php
/**
 * Dashboard snapshot repository.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reads and writes redacted status snapshots.
 *
 * @since 0.1.0
 */
class Alynt_Drime_Backups_Dashboard_Snapshot_Repository {
	/**
	 * Records a status snapshot.
	 *
	 * @since 0.1.0
	 *
	 * @param int    $site_id Site ID.
	 * @param array  $payload Redacted status payload.
	 * @param string $status_category Dashboard status category.
	 * @return int|WP_Error Inserted snapshot ID, or an error when storage fails.
	 */
	public function record( $site_id, array $payload, $status_category ) {
		global $wpdb;

		$encoded_payload = wp_json_encode( $payload );
		$table           = Alynt_Drime_Backups_Dashboard_Storage::snapshots_table();

		if ( false === $encoded_payload ) {
			return new WP_Error( 'snapshot_payload_encode_failed', __( 'The client status payload could not be prepared for storage.', 'alynt-drime-backups-dashboard' ) );
		}

		$inserted = $wpdb->insert(
			$table,
			array(
				'dashboard_site_id'   => (int) $site_id,
				'schema_version'      => isset( $payload['schema_version'] ) ? (int) $payload['schema_version'] : 1,
				'observed_at'         => current_time( 'mysql', true ),
				'payload_fingerprint' => hash( 'sha256', (string) $encoded_payload ),
				'overall_status'      => sanitize_key( $status_category ),
				'queue_count'         => isset( $payload['queue_count'] ) ? max( 0, (int) $payload['queue_count'] ) : 0,
				'uploaded_count'      => isset( $payload['uploaded_count'] ) ? max( 0, (int) $payload['uploaded_count'] ) : 0,
				'failed_count'        => isset( $payload['failed_count'] ) ? max( 0, (int) $payload['failed_count'] ) : 0,
				'active_upload'       => ! empty( $payload['active_upload'] ) ? 1 : 0,
				'warning_count'       => isset( $payload['warning_count'] ) ? max( 0, (int) $payload['warning_count'] ) : 0,
				'cron_status'         => isset( $payload['cron_status'] ) ? sanitize_key( $payload['cron_status'] ) : '',
				'payload_json'        => (string) $encoded_payload,
			),
			array( '%d', '%d', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%s', '%s' )
		);

		if ( false === $inserted || empty( $wpdb->insert_id ) ) {
			return new WP_Error(
				'snapshot_store_failed',
				__( 'The dashboard could not store the client status snapshot.', 'alynt-drime-backups-dashboard' ),
				array(
					'last_error' => isset( $wpdb->last_error ) ? sanitize_text_field( $wpdb->last_error ) : '',
				)
			);
		}

		return (int) $wpdb->insert_id;
	}

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
