<?php
/**
 * Dashboard site repository read helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reads dashboard-owned client site records.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Site_Repository_Reads {
	/**
	 * Lists dashboard sites.
	 *
	 * @since 0.1.0
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
		$fields = array(
			'id',
			'public_id',
			'site_uuid',
			'site_label',
			'expected_origin',
			'environment',
			'enrollment_status',
			'polling_key_id',
			"CASE WHEN polling_secret_ciphertext IS NULL OR polling_secret_ciphertext = '' THEN 0 ELSE 1 END AS has_polling_secret",
			'plugin_version',
			'payload_schema_version',
			'overall_status',
			'last_poll_attempt_at',
			'last_seen_at',
			'next_poll_at',
			'consecutive_failures',
			'last_error_code',
			'last_error_summary',
			'paused_at',
			'created_at',
			'updated_at',
		);
		$sql    = 'SELECT ' . implode( ', ', $fields ) . " FROM {$table} {$where} ORDER BY site_label ASC, expected_origin ASC LIMIT %d OFFSET %d";

		$params[] = $limit;
		$params[] = $offset;

		return $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );
	}

	/**
	 * Gets one site by ID.
	 *
	 * @since 0.1.0
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
	 * Gets enrolled sites that are due for polling.
	 *
	 * @since 0.1.0
	 *
	 * @param int    $limit Maximum rows.
	 * @param string $now Current UTC datetime.
	 * @return array<int,array<string,mixed>>
	 */
	public function due_for_poll( $limit = 5, $now = '' ) {
		global $wpdb;

		$limit = max( 1, min( 50, (int) $limit ) );
		$now   = '' !== $now ? sanitize_text_field( $now ) : current_time( 'mysql', true );
		$table = Alynt_Drime_Backups_Dashboard_Storage::sites_table();

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table}
				WHERE enrollment_status IN (%s, %s)
					AND paused_at IS NULL
					AND polling_key_id IS NOT NULL
					AND polling_secret_ciphertext IS NOT NULL
					AND (next_poll_at IS NULL OR next_poll_at <= %s)
				ORDER BY COALESCE(next_poll_at, '1970-01-01 00:00:00') ASC, id ASC
				LIMIT %d",
				'active',
				Alynt_Drime_Backups_Dashboard_Enrollment_REST_Controller::ENROLLMENT_STATUS_AWAITING_FIRST_POLL,
				$now,
				$limit
			),
			ARRAY_A
		);
	}

	/**
	 * Gets a pending site by public enrollment ID.
	 *
	 * @since 0.1.0
	 *
	 * @param string $public_id Public ID.
	 * @return array<string,mixed>|null
	 */
	public function get_pending_by_public_id( $public_id ) {
		global $wpdb;

		$table = Alynt_Drime_Backups_Dashboard_Storage::sites_table();
		$row   = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE public_id = %s AND enrollment_status = %s LIMIT 1",
				sanitize_text_field( $public_id ),
				'pending'
			),
			ARRAY_A
		);

		return $row ? $row : null;
	}

	/**
	 * Gets a non-expired pending site for a client origin.
	 *
	 * @since 0.1.0
	 *
	 * @param string $expected_origin Canonical expected client origin.
	 * @param string $now Current UTC datetime.
	 * @return array<string,mixed>|null
	 */
	public function get_active_pending_by_expected_origin( $expected_origin, $now = '' ) {
		global $wpdb;

		$table = Alynt_Drime_Backups_Dashboard_Storage::sites_table();
		$now   = '' !== $now ? sanitize_text_field( $now ) : current_time( 'mysql', true );
		$row   = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE expected_origin = %s AND enrollment_status = %s AND pairing_expires_at > %s LIMIT 1",
				esc_url_raw( $expected_origin ),
				'pending',
				$now
			),
			ARRAY_A
		);

		return $row ? $row : null;
	}
}
