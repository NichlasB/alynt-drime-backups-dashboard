<?php
/**
 * Dashboard site repository.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reads and writes dashboard-owned client site records.
 *
 * @since 0.1.0
 */
class Alynt_Drime_Backups_Dashboard_Site_Repository {
	/**
	 * Lists dashboard sites.
	 *
	 * @param array $args Query args.
	 * @return array<int,array<string,mixed>>
	 */
	public function all( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'status' => '',
			'limit'  => 100,
			'offset' => 0,
		);

		$args   = wp_parse_args( $args, $defaults );
		$where  = 'WHERE 1=1';
		$params = array();

		if ( '' !== $args['status'] ) {
			$where   .= ' AND enrollment_status = %s';
			$params[] = $args['status'];
		}

		$limit  = max( 1, min( 500, (int) $args['limit'] ) );
		$offset = max( 0, (int) $args['offset'] );
		$table  = Alynt_Drime_Backups_Dashboard_Storage::sites_table();
		$sql    = "SELECT * FROM {$table} {$where} ORDER BY site_label ASC, expected_origin ASC LIMIT %d OFFSET %d";

		$params[] = $limit;
		$params[] = $offset;

		return $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );
	}

	/**
	 * Gets one site by ID.
	 *
	 * @param int $site_id Site ID.
	 * @return array<string,mixed>|null
	 */
	public function get( $site_id ) {
		global $wpdb;

		$table = Alynt_Drime_Backups_Dashboard_Storage::sites_table();
		$row   = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE id = %d",
				(int) $site_id
			),
			ARRAY_A
		);

		return $row ? $row : null;
	}

	/**
	 * Creates a pending site placeholder.
	 *
	 * @param array $data Site data.
	 * @return int Inserted site ID.
	 */
	public function create_pending( array $data ) {
		global $wpdb;

		$now   = current_time( 'mysql', true );
		$table = Alynt_Drime_Backups_Dashboard_Storage::sites_table();

		$wpdb->insert(
			$table,
			array(
				'site_uuid'           => ! empty( $data['site_uuid'] ) ? sanitize_text_field( $data['site_uuid'] ) : null,
				'public_id'           => isset( $data['public_id'] ) ? sanitize_text_field( $data['public_id'] ) : '',
				'site_label'          => isset( $data['site_label'] ) ? sanitize_text_field( $data['site_label'] ) : '',
				'expected_origin'     => isset( $data['expected_origin'] ) ? esc_url_raw( $data['expected_origin'] ) : '',
				'environment'         => isset( $data['environment'] ) ? sanitize_key( $data['environment'] ) : 'production',
				'enrollment_status'   => isset( $data['enrollment_status'] ) ? sanitize_key( $data['enrollment_status'] ) : 'pending',
				'overall_status'      => isset( $data['overall_status'] ) ? sanitize_key( $data['overall_status'] ) : 'pending',
				'pairing_secret_hash' => isset( $data['pairing_secret_hash'] ) ? sanitize_text_field( $data['pairing_secret_hash'] ) : null,
				'pairing_expires_at'  => isset( $data['pairing_expires_at'] ) ? sanitize_text_field( $data['pairing_expires_at'] ) : null,
				'created_at'          => $now,
				'updated_at'          => $now,
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		return (int) $wpdb->insert_id;
	}

	/**
	 * Locally revokes a pending or enrolled site without contacting the client.
	 *
	 * @param int $site_id Site ID.
	 * @return bool
	 */
	public function revoke_local( $site_id ) {
		global $wpdb;

		$now   = current_time( 'mysql', true );
		$table = Alynt_Drime_Backups_Dashboard_Storage::sites_table();

		return false !== $wpdb->update(
			$table,
			array(
				'enrollment_status'         => 'revoked',
				'overall_status'            => 'pending',
				'pairing_secret_hash'       => null,
				'pairing_expires_at'        => null,
				'polling_key_id'            => null,
				'polling_secret_ciphertext' => null,
				'next_poll_at'              => null,
				'updated_at'                => $now,
			),
			array( 'id' => (int) $site_id ),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Updates polling evidence for a site.
	 *
	 * @param int    $site_id Site ID.
	 * @param string $status Status slug.
	 * @param string $last_error Last error message.
	 * @return bool
	 */
	public function mark_polled( $site_id, $status, $last_error = '' ) {
		global $wpdb;

		$now   = current_time( 'mysql', true );
		$table = Alynt_Drime_Backups_Dashboard_Storage::sites_table();

		return false !== $wpdb->update(
			$table,
			array(
				'overall_status'       => sanitize_key( $status ),
				'last_poll_attempt_at' => $now,
				'last_seen_at'         => $now,
				'last_error_summary'   => '' === $last_error ? null : sanitize_textarea_field( $last_error ),
				'updated_at'           => $now,
			),
			array( 'id' => (int) $site_id ),
			array( '%s', '%s', '%s', '%s', '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Counts rows by status.
	 *
	 * @return array<string,int>
	 */
	public function counts_by_status() {
		global $wpdb;

		$table  = Alynt_Drime_Backups_Dashboard_Storage::sites_table();
		$rows   = $wpdb->get_results( "SELECT overall_status, COUNT(*) AS total FROM {$table} GROUP BY overall_status", ARRAY_A );
		$counts = array();

		foreach ( $rows as $row ) {
			$counts[ (string) $row['overall_status'] ] = (int) $row['total'];
		}

		return $counts;
	}

	/**
	 * Deletes one dashboard site row.
	 *
	 * The caller must delete dependent snapshots first when needed.
	 *
	 * @param int $site_id Site ID.
	 * @return bool
	 */
	public function delete( $site_id ) {
		global $wpdb;

		$table = Alynt_Drime_Backups_Dashboard_Storage::sites_table();

		return false !== $wpdb->delete(
			$table,
			array( 'id' => (int) $site_id ),
			array( '%d' )
		);
	}
}
