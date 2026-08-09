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
	 * @param int    $site_id Site ID.
	 * @param array  $payload Redacted status payload.
	 * @param string $status_category Dashboard status category.
	 * @return int Inserted snapshot ID.
	 */
	public function record( $site_id, array $payload, $status_category ) {
		global $wpdb;

		$encoded_payload = wp_json_encode( $payload );
		$table           = Alynt_Drime_Backups_Dashboard_Storage::snapshots_table();

		$wpdb->insert(
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

		return (int) $wpdb->insert_id;
	}

	/**
	 * Gets the latest snapshot for one site.
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
	 * Counts snapshots for one site.
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
	 * Deletes snapshots for one site.
	 *
	 * @param int $site_id Site ID.
	 * @return bool
	 */
	public function delete_for_site( $site_id ) {
		global $wpdb;

		$table = Alynt_Drime_Backups_Dashboard_Storage::snapshots_table();

		return false !== $wpdb->delete(
			$table,
			array( 'dashboard_site_id' => (int) $site_id ),
			array( '%d' )
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
