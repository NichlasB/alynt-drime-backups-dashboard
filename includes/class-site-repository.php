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
			$where   .= ' AND status = %s';
			$params[] = $args['status'];
		}

		$limit  = max( 1, min( 500, (int) $args['limit'] ) );
		$offset = max( 0, (int) $args['offset'] );
		$table  = Alynt_Drime_Backups_Dashboard_Storage::sites_table();
		$sql    = "SELECT * FROM {$table} {$where} ORDER BY display_name ASC, site_url ASC LIMIT %d OFFSET %d";

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
				'site_uuid'           => isset( $data['site_uuid'] ) ? sanitize_text_field( $data['site_uuid'] ) : '',
				'site_url'            => isset( $data['site_url'] ) ? esc_url_raw( $data['site_url'] ) : '',
				'display_name'        => isset( $data['display_name'] ) ? sanitize_text_field( $data['display_name'] ) : '',
				'status'              => isset( $data['status'] ) ? sanitize_key( $data['status'] ) : 'pending',
				'polling_secret_enc'  => isset( $data['polling_secret_enc'] ) ? (string) $data['polling_secret_enc'] : null,
				'polling_secret_hint' => isset( $data['polling_secret_hint'] ) ? sanitize_text_field( $data['polling_secret_hint'] ) : '',
				'created_at'          => $now,
				'updated_at'          => $now,
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		return (int) $wpdb->insert_id;
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
				'status'         => sanitize_key( $status ),
				'last_polled_at' => $now,
				'last_seen_at'   => $now,
				'last_error'     => '' === $last_error ? null : sanitize_textarea_field( $last_error ),
				'updated_at'     => $now,
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
		$rows   = $wpdb->get_results( "SELECT status, COUNT(*) AS total FROM {$table} GROUP BY status", ARRAY_A );
		$counts = array();

		foreach ( $rows as $row ) {
			$counts[ (string) $row['status'] ] = (int) $row['total'];
		}

		return $counts;
	}
}
