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
				'site_id'         => (int) $site_id,
				'schema_version'  => isset( $payload['schema_version'] ) ? (int) $payload['schema_version'] : 1,
				'status_category' => sanitize_key( $status_category ),
				'payload_hash'    => hash( 'sha256', (string) $encoded_payload ),
				'status_payload'  => (string) $encoded_payload,
				'captured_at'     => current_time( 'mysql', true ),
			),
			array( '%d', '%d', '%s', '%s', '%s', '%s' )
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
				"SELECT * FROM {$table} WHERE site_id = %d ORDER BY captured_at DESC, id DESC LIMIT 1",
				(int) $site_id
			),
			ARRAY_A
		);

		if ( ! $row ) {
			return null;
		}

		$row['decoded_payload'] = $this->decode_payload( $row['status_payload'] );

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
					SELECT site_id, MAX(id) AS latest_id
					FROM {$table}
					WHERE site_id IN ({$placeholders})
					GROUP BY site_id
				) latest ON latest.latest_id = s1.id",
				$site_ids
			),
			ARRAY_A
		);

		$snapshots = array();

		foreach ( $rows as $row ) {
			$row['decoded_payload']             = $this->decode_payload( $row['status_payload'] );
			$snapshots[ (int) $row['site_id'] ] = $row;
		}

		return $snapshots;
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
