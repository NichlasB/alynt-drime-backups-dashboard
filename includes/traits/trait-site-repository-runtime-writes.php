<?php
/**
 * Dashboard site repository runtime write helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Writes polling and runtime dashboard-owned client site state.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Site_Repository_Runtime_Writes {
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
	 * Stores a dashboard-owned V2 action signing key for one enrolled site.
	 *
	 * @since 0.1.15
	 *
	 * @param int    $site_id Site ID.
	 * @param string $action_key_id Action key ID.
	 * @param string $private_key_ciphertext Encrypted private key.
	 * @return bool
	 */
	public function store_action_signing_key( $site_id, $action_key_id, $private_key_ciphertext ) {
		global $wpdb;

		$now   = current_time( 'mysql', true );
		$table = Alynt_Drime_Backups_Dashboard_Storage::sites_table();

		$updated = $wpdb->update(
			$table,
			array(
				'action_key_id'                 => sanitize_text_field( (string) $action_key_id ),
				'action_private_key_ciphertext' => (string) $private_key_ciphertext,
				'updated_at'                    => $now,
			),
			array( 'id' => (int) $site_id ),
			array( '%s', '%s', '%s' ),
			array( '%d' )
		);

		return $this->update_changed_existing_row( $updated );
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
