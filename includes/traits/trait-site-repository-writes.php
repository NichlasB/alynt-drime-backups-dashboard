<?php
/**
 * Dashboard site repository write helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Writes dashboard-owned client site records.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Site_Repository_Writes {
	/**
	 * Creates a pending site placeholder.
	 *
	 * @since 0.1.0
	 *
	 * @param array $data Site data.
	 * @return int|WP_Error Inserted site ID, or an error when storage fails.
	 */
	public function create_pending( array $data ) {
		global $wpdb;
		$now      = current_time( 'mysql', true );
		$table    = Alynt_Drime_Backups_Dashboard_Storage::sites_table();
		$inserted = $wpdb->insert(
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

		if ( false === $inserted || empty( $wpdb->insert_id ) ) {
			return new WP_Error( 'site_create_failed', __( 'The dashboard could not create the pending site record. Please try again before sharing a pairing token.', 'alynt-drime-backups-dashboard' ) );
		}

		return (int) $wpdb->insert_id;
	}

	/**
	 * Locally revokes a pending or enrolled site without contacting the client.
	 *
	 * @since 0.1.0
	 *
	 * @param int $site_id Site ID.
	 * @return bool
	 */
	public function revoke_local( $site_id ) {
		global $wpdb;

		$now   = current_time( 'mysql', true );
		$table = Alynt_Drime_Backups_Dashboard_Storage::sites_table();

		$updated = $wpdb->update(
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

		return $this->update_changed_existing_row( $updated );
	}

	/**
	 * Completes enrollment state while waiting for first valid poll activation.
	 *
	 * @since 0.1.0
	 *
	 * @param int                 $site_id Site ID.
	 * @param array<string,mixed> $data Enrollment data.
	 * @return bool
	 */
	public function complete_enrollment_pending_first_poll( $site_id, array $data ) {
		global $wpdb;

		$now   = current_time( 'mysql', true );
		$table = Alynt_Drime_Backups_Dashboard_Storage::sites_table();

		$updated = $wpdb->update(
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

		return $this->update_changed_existing_row( $updated );
	}

	/**
	 * Marks a status poll as successful.
	 *
	 * @since 0.1.0
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
	 * @since 0.1.0
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

	/**
	 * Confirms a write touched an existing row.
	 *
	 * @since 0.1.0
	 *
	 * @param int|false $updated Update result from wpdb.
	 * @return bool
	 */
	private function update_changed_existing_row( $updated ) {
		return false !== $updated && (int) $updated > 0;
	}
}
