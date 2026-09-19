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
				'enrollment_status'             => 'revoked',
				'overall_status'                => 'pending',
				'pairing_secret_hash'           => null,
				'pairing_expires_at'            => null,
				'polling_key_id'                => null,
				'polling_secret_ciphertext'     => null,
				'action_key_id'                 => null,
				'action_private_key_ciphertext' => null,
				'next_poll_at'                  => null,
				'updated_at'                    => $now,
			),
			array( 'id' => (int) $site_id ),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ),
			array( '%d' )
		);

		return $this->update_changed_existing_row( $updated );
	}

	/**
	 * Archives a non-polling dashboard-owned record locally.
	 *
	 * @since 0.1.37
	 *
	 * @param int $site_id Site ID.
	 * @return bool
	 */
	public function archive_local( $site_id ) {
		global $wpdb;

		$now   = current_time( 'mysql', true );
		$table = Alynt_Drime_Backups_Dashboard_Storage::sites_table();

		$updated = $wpdb->update(
			$table,
			array(
				'archived_at' => $now,
				'updated_at'  => $now,
			),
			array( 'id' => (int) $site_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		return $this->update_changed_existing_row( $updated );
	}

	/**
	 * Restores a locally archived dashboard-owned record to visible local history.
	 *
	 * @since 0.1.37
	 *
	 * @param int $site_id Site ID.
	 * @return bool
	 */
	public function unarchive_local( $site_id ) {
		global $wpdb;

		$now   = current_time( 'mysql', true );
		$table = Alynt_Drime_Backups_Dashboard_Storage::sites_table();

		$updated = $wpdb->update(
			$table,
			array(
				'archived_at' => null,
				'updated_at'  => $now,
			),
			array( 'id' => (int) $site_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		return $this->update_changed_existing_row( $updated );
	}

	/**
	 * Pauses scheduled polling for one dashboard-owned site record.
	 *
	 * @since 0.1.27
	 *
	 * @param int $site_id Site ID.
	 * @return bool
	 */
	public function pause_polling( $site_id ) {
		global $wpdb;

		$now   = current_time( 'mysql', true );
		$table = Alynt_Drime_Backups_Dashboard_Storage::sites_table();

		$updated = $wpdb->update(
			$table,
			array(
				'paused_at'    => $now,
				'next_poll_at' => null,
				'updated_at'   => $now,
			),
			array( 'id' => (int) $site_id ),
			array( '%s', '%s', '%s' ),
			array( '%d' )
		);

		return $this->update_changed_existing_row( $updated );
	}

	/**
	 * Resumes scheduled polling for one dashboard-owned site record.
	 *
	 * @since 0.1.27
	 *
	 * @param int $site_id Site ID.
	 * @return bool
	 */
	public function resume_polling( $site_id ) {
		global $wpdb;

		$now   = current_time( 'mysql', true );
		$table = Alynt_Drime_Backups_Dashboard_Storage::sites_table();

		$updated = $wpdb->update(
			$table,
			array(
				'paused_at'    => null,
				'next_poll_at' => $now,
				'updated_at'   => $now,
			),
			array( 'id' => (int) $site_id ),
			array( '%s', '%s', '%s' ),
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
}
