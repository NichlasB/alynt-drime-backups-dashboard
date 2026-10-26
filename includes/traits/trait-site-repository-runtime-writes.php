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
	 * @param int                 $site_id Site ID.
	 * @param string              $status Dashboard status category.
	 * @param string              $plugin_version Uploader version.
	 * @param string              $next_poll_at Next scheduled poll time.
	 * @param array<string,mixed> $expected_site Captured polling context; required for admission.
	 * @return bool|WP_Error False on storage failure; error on stale context.
	 */
	public function mark_poll_success( $site_id, $status, $plugin_version = '', $next_poll_at = '', array $expected_site = array() ) {
		$now  = current_time( 'mysql', true );
		$next = '' !== $next_poll_at ? sanitize_text_field( $next_poll_at ) : null;

		return $this->write_poll_completion(
			$site_id,
			$expected_site,
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
			true
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
	 * @param int                 $site_id Site ID.
	 * @param string              $error_code Stable error code.
	 * @param string              $summary Operator-safe summary.
	 * @param string              $next_poll_at Next retry time.
	 * @param int                 $consecutive_failures Consecutive failure count.
	 * @param array<string,mixed> $expected_site Captured polling context; required for admission.
	 * @return bool|WP_Error False on storage failure; error on stale context.
	 */
	public function mark_poll_failure( $site_id, $error_code, $summary = '', $next_poll_at = '', $consecutive_failures = 1, array $expected_site = array() ) {
		$now  = current_time( 'mysql', true );
		$next = '' !== $next_poll_at ? sanitize_text_field( $next_poll_at ) : null;

		return $this->write_poll_completion(
			$site_id,
			$expected_site,
			array(
				'overall_status'       => 'needs_attention',
				'last_poll_attempt_at' => $now,
				'next_poll_at'         => $next,
				'last_error_code'      => sanitize_key( $error_code ),
				'last_error_summary'   => sanitize_text_field( $summary ),
				'consecutive_failures' => max( 1, (int) $consecutive_failures ),
				'updated_at'           => $now,
			),
			false
		);
	}

	/**
	 * Fences the write itself, not a pre-read or advisory transient lock.
	 *
	 * @param int                 $site_id Site ID.
	 * @param array<string,mixed> $expected Captured enrollment and credentials.
	 * @param array<string,mixed> $data Fixed internal completion fields.
	 * @param bool                $success Whether usable credentials are required.
	 * @return bool|WP_Error
	 */
	private function write_poll_completion( $site_id, array $expected, array $data, $success ) {
		global $wpdb;

		if ( ! ( is_int( $site_id ) || is_string( $site_id ) ) || (int) $site_id <= 0 || (string) (int) $site_id !== (string) $site_id || ! isset( $expected['id'], $expected['enrollment_status'] ) || ! ( is_int( $expected['id'] ) || is_string( $expected['id'] ) ) || (string) $site_id !== (string) $expected['id'] || ! in_array( $expected['enrollment_status'], array( 'active', 'awaiting_first_poll' ), true ) ) {
			return $this->stale_poll_completion();
		}

		$context = array( 'id' => (int) $site_id );
		foreach ( array( 'public_id', 'polling_key_id', 'polling_secret_ciphertext' ) as $field ) {
			if ( ! array_key_exists( $field, $expected ) || ( null !== $expected[ $field ] && ! is_string( $expected[ $field ] ) ) || ( ( $success || 'public_id' === $field ) && empty( $expected[ $field ] ) ) ) {
				return $this->stale_poll_completion();
			}
			$context[ $field ] = $expected[ $field ];
		}

		$table       = Alynt_Drime_Backups_Dashboard_Storage::sites_table();
		$conditions  = $this->poll_completion_sql_fields( $context, true ) . " AND BINARY enrollment_status IN ('active', 'awaiting_first_poll')";
		$assignments = $this->poll_completion_sql_fields( $data, false );
		// Fixed storage identifier/column maps only; all values are individually prepared.
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
		$updated = $wpdb->query( "UPDATE {$table} SET {$assignments} WHERE {$conditions}" );
		if ( false === $updated ) {
			return false;
		}
		if ( $updated > 0 ) {
			return true;
		}

		// Zero can mean an unchanged row, a revoked/rotated row, or a missing row.
		// Verify the entire intended state under the same fence, never mere existence.
		$persisted = $this->poll_completion_sql_fields( $data, true );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
		$unchanged = $wpdb->get_var( "SELECT 1 FROM {$table} WHERE {$conditions} AND {$persisted} LIMIT 1" );
		if ( '' !== $wpdb->last_error ) {
			return false;
		}
		return '1' === (string) $unchanged ? true : $this->stale_poll_completion();
	}

	/**
	 * Renders only fixed internal field maps; opaque values compare byte-for-byte.
	 *
	 * @param array<string,mixed> $fields Internal columns and values, never request keys.
	 * @param bool                $where Whether rendering a predicate instead of SET.
	 * @return string
	 */
	private function poll_completion_sql_fields( array $fields, $where ) {
		global $wpdb;

		$parts = array();
		foreach ( $fields as $column => $value ) {
			if ( null === $value ) {
				$parts[] = $column . ( $where ? ' IS NULL' : ' = NULL' );
			} else {
				$prefix = $where && is_string( $value ) ? 'BINARY ' : '';
				$format = is_int( $value ) ? '%d' : '%s';
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Only the format is dynamic; columns are closed internal maps.
				$parts[] = $prefix . $column . ' = ' . $wpdb->prepare( $format, $value );
			}
		}
		return implode( $where ? ' AND ' : ', ', $parts );
	}

	/**
	 * Returns a safe non-storage error without credentials or database details.
	 *
	 * @return WP_Error
	 */
	private function stale_poll_completion() {
		return new WP_Error( 'poll_completion_stale', __( 'The status check was not applied because the site enrollment or polling credentials changed. Please refresh the site before trying again.', 'alynt-drime-backups-dashboard' ) );
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
