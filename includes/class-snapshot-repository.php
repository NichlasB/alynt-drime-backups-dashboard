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
	use Alynt_Drime_Backups_Dashboard_Snapshot_Repository_Reads;
	use Alynt_Drime_Backups_Dashboard_Snapshot_Repository_Retention;

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
}
