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
	 * Gets enrolled sites that are due for polling.
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
	 * Completes enrollment state while waiting for first valid poll activation.
	 *
	 * @param int                 $site_id Site ID.
	 * @param array<string,mixed> $data Enrollment data.
	 * @return bool
	 */
	public function complete_enrollment_pending_first_poll( $site_id, array $data ) {
		global $wpdb;

		$now   = current_time( 'mysql', true );
		$table = Alynt_Drime_Backups_Dashboard_Storage::sites_table();

		return false !== $wpdb->update(
			$table,
			array(
				'site_uuid'                 => isset( $data['site_uuid'] ) ? sanitize_text_field( $data['site_uuid'] ) : null,
				'enrollment_status'         => Alynt_Drime_Backups_Dashboard_Enrollment_REST_Controller::ENROLLMENT_STATUS_AWAITING_FIRST_POLL,
				'overall_status'            => 'pending',
				'pairing_secret_hash'       => null,
				'pairing_expires_at'        => null,
				'polling_key_id'            => isset( $data['polling_key_id'] ) ? sanitize_text_field( $data['polling_key_id'] ) : null,
				'polling_secret_ciphertext' => isset( $data['polling_secret_ciphertext'] ) ? (string) $data['polling_secret_ciphertext'] : null,
				'plugin_version'            => isset( $data['plugin_version'] ) ? sanitize_text_field( $data['plugin_version'] ) : null,
				'payload_schema_version'    => isset( $data['payload_schema_version'] ) ? absint( $data['payload_schema_version'] ) : null,
				'updated_at'                => $now,
			),
			array(
				'id'                => (int) $site_id,
				'enrollment_status' => 'pending',
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s' ),
			array( '%d', '%s' )
		);
	}

	/**
	 * Marks a status poll as successful.
	 *
	 * @param int    $site_id Site ID.
	 * @param string $status Dashboard status category.
	 * @param string $plugin_version Uploader version.
	 * @param string $next_poll_at Next scheduled poll time.
	 * @return bool
	 */
	public function mark_poll_success( $site_id, $status, $plugin_version = '', $next_poll_at = '' ) {
		global $wpdb;

		$now   = current_time( 'mysql', true );
		$table = Alynt_Drime_Backups_Dashboard_Storage::sites_table();
		$next  = '' !== $next_poll_at ? sanitize_text_field( $next_poll_at ) : null;

		return false !== $wpdb->update(
			$table,
			array(
				'enrollment_status'    => 'active',
				'overall_status'       => sanitize_key( $status ),
				'plugin_version'       => sanitize_text_field( $plugin_version ),
				'last_poll_attempt_at' => $now,
				'last_seen_at'         => $now,
				'next_poll_at'         => $next,
				'consecutive_failures' => 0,
				'last_error_code'      => null,
				'last_error_summary'   => null,
				'updated_at'           => $now,
			),
			array( 'id' => (int) $site_id ),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Marks a status poll failure with safe error metadata.
	 *
	 * @param int    $site_id Site ID.
	 * @param string $error_code Stable error code.
	 * @param string $summary Operator-safe summary.
	 * @param string $next_poll_at Next retry time.
	 * @param int    $consecutive_failures Consecutive failure count.
	 * @return bool
	 */
	public function mark_poll_failure( $site_id, $error_code, $summary = '', $next_poll_at = '', $consecutive_failures = 1 ) {
		global $wpdb;

		$now   = current_time( 'mysql', true );
		$table = Alynt_Drime_Backups_Dashboard_Storage::sites_table();
		$next  = '' !== $next_poll_at ? sanitize_text_field( $next_poll_at ) : null;

		return false !== $wpdb->update(
			$table,
			array(
				'overall_status'       => 'needs_attention',
				'last_poll_attempt_at' => $now,
				'next_poll_at'         => $next,
				'last_error_code'      => sanitize_key( $error_code ),
				'last_error_summary'   => sanitize_text_field( $summary ),
				'consecutive_failures' => max( 1, (int) $consecutive_failures ),
				'updated_at'           => $now,
			),
			array( 'id' => (int) $site_id ),
			array( '%s', '%s', '%s', '%s', '%s', '%d', '%s' ),
			array( '%d' )
		);
	}
}
