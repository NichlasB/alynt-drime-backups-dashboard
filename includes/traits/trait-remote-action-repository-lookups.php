<?php
/**
 * Remote action repository helper trait.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.26
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *
Handles remote action lookup and recent-history query methods.
 *
 * @since 0.1.26
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Lookups {


	/**
	 * Finds one dashboard action by public ID and dashboard site.
	 *
	 * @since 0.1.16
	 *
	 * @param string $public_id Public action UUID.
	 * @param int    $site_id Dashboard site ID.
	 * @return array<string,mixed>|null
	 */
	public function find_by_public_id_for_site( $public_id, $site_id ) {
		global $wpdb;

		$public_id = $this->sanitize_uuid( $public_id );
		$site_id   = absint( $site_id );

		if ( '' === $public_id || 0 === $site_id ) {
			return null;
		}

		$table = Alynt_Drime_Backups_Dashboard_Storage::actions_table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Repository reads a plugin-owned custom table; callers own caching decisions.
		$row = $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is produced by Storage for a plugin-owned custom table.
				"SELECT * FROM {$table} WHERE public_id = %s AND dashboard_site_id = %d LIMIT 1",
				$public_id,
				$site_id
			),
			ARRAY_A
		);

		return is_array( $row ) ? $row : null;
	}

	/**
	 * Gets one fresh successful schedule preview that may be used for apply.
	 *
	 * @since 0.1.24
	 *
	 * @param int                 $site_id Site ID.
	 * @param string              $preview_public_id Preview action public ID.
	 * @param array<string,mixed> $capabilities Latest sanitized remote-action capabilities.
	 * @param string|null         $now Current UTC MySQL timestamp.
	 * @return array<string,mixed>|WP_Error
	 */
	public function fresh_schedule_preview_for_apply( $site_id, $preview_public_id, array $capabilities, $now = null ) {
		$site_id           = absint( $site_id );
		$preview_public_id = $this->sanitize_uuid( $preview_public_id );

		if ( 0 === $site_id || '' === $preview_public_id ) {
			return new WP_Error( 'schedule_apply_preview_missing', __( 'Choose a fresh schedule preview before applying a schedule change.', 'alynt-drime-backups-dashboard' ) );
		}

		$row = $this->find_by_public_id_for_site( $preview_public_id, $site_id );

		if ( ! is_array( $row ) || Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCHEDULE_PREVIEW !== $this->capabilities->sanitize_action_type( isset( $row['action_type'] ) ? (string) $row['action_type'] : '' ) ) {
			return new WP_Error( 'schedule_apply_preview_missing', __( 'The selected schedule preview could not be found. Run a new preview before applying a schedule change.', 'alynt-drime-backups-dashboard' ) );
		}

		if ( 'succeeded' !== $this->capabilities->sanitize_state( isset( $row['state'] ) ? (string) $row['state'] : '' ) ) {
			return new WP_Error( 'schedule_apply_preview_not_ready', __( 'The selected schedule preview has not succeeded yet. Run Check Now and confirm the preview result before applying.', 'alynt-drime-backups-dashboard' ) );
		}

		$context = $this->context_from_row( $row );
		$preview = isset( $context['schedule_preview'] ) && is_array( $context['schedule_preview'] ) ? $context['schedule_preview'] : array();

		if ( empty( $preview['would_change'] ) ) {
			return new WP_Error( 'schedule_apply_preview_not_applicable', __( 'The selected preview does not describe a schedule change to apply.', 'alynt-drime-backups-dashboard' ) );
		}

		$schedule_id         = isset( $preview['schedule_id'] ) ? sanitize_key( (string) $preview['schedule_id'] ) : '';
		$proposed_cadence    = isset( $preview['proposed_cadence'] ) ? sanitize_key( (string) $preview['proposed_cadence'] ) : '';
		$preview_action_id   = isset( $preview['preview_action_id'] ) ? $this->sanitize_uuid( (string) $preview['preview_action_id'] ) : '';
		$preview_fingerprint = isset( $preview['preview_fingerprint'] ) ? $this->sha256_or_empty( (string) $preview['preview_fingerprint'] ) : '';
		$capability_version  = isset( $preview['capability_version'] ) ? absint( $preview['capability_version'] ) : 0;

		if (
			Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::SCHEDULE_SCAN_UPLOAD !== $schedule_id
			|| '' === $proposed_cadence
			|| ( '' !== $preview_action_id && $preview_public_id !== $preview_action_id )
			|| '' === $preview_fingerprint
			|| $capability_version < 1
		) {
			return new WP_Error( 'schedule_apply_preview_invalid', __( 'The selected preview is missing required safe apply evidence. Run a new preview before applying.', 'alynt-drime-backups-dashboard' ) );
		}

		$now_timestamp = strtotime( null === $now ? gmdate( 'Y-m-d H:i:s' ) : (string) $now );
		if ( false === $now_timestamp ) {
			$now_timestamp = time();
		}

		$preview_expires_at = isset( $preview['preview_expires_at'] ) ? strtotime( (string) $preview['preview_expires_at'] ) : false;
		$completed_at       = isset( $row['completed_at'] ) ? strtotime( (string) $row['completed_at'] ) : false;

		if ( false !== $preview_expires_at && $preview_expires_at <= $now_timestamp ) {
			return new WP_Error( 'schedule_apply_preview_expired', __( 'The selected schedule preview has expired. Run a new preview before applying.', 'alynt-drime-backups-dashboard' ) );
		}

		if ( false !== $completed_at && $completed_at < ( $now_timestamp - self::SCHEDULE_PREVIEW_FRESH_SECONDS ) ) {
			return new WP_Error( 'schedule_apply_preview_expired', __( 'The selected schedule preview is no longer fresh. Run a new preview before applying.', 'alynt-drime-backups-dashboard' ) );
		}

		if ( ! $this->capabilities->supports_schedule_apply_action( $capabilities, $schedule_id, $proposed_cadence ) ) {
			return new WP_Error( 'schedule_apply_unavailable', __( 'The latest client report does not allow applying this previewed schedule change. Run Check Now after enabling Schedule Apply on the client.', 'alynt-drime-backups-dashboard' ) );
		}

		return array(
			'preview_action_id'    => $preview_public_id,
			'preview_fingerprint'  => $preview_fingerprint,
			'schedule_id'          => $schedule_id,
			'current_cadence'      => isset( $preview['current_cadence'] ) ? sanitize_key( (string) $preview['current_cadence'] ) : '',
			'proposed_cadence'     => $proposed_cadence,
			'capability_version'   => $capability_version,
			'preview_expires_at'   => isset( $preview['preview_expires_at'] ) ? sanitize_text_field( (string) $preview['preview_expires_at'] ) : '',
			'preview_completed_at' => isset( $row['completed_at'] ) ? sanitize_text_field( (string) $row['completed_at'] ) : '',
		);
	}

	/**
	 * Gets latest action for one site.
	 *
	 * @since 0.1.15
	 *
	 * @param int $site_id Site ID.
	 * @return array<string,mixed>|null
	 */
	public function latest_for_site( $site_id ) {
		global $wpdb;

		$site_id = absint( $site_id );

		if ( 0 === $site_id ) {
			return null;
		}

		$table = Alynt_Drime_Backups_Dashboard_Storage::actions_table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Repository reads a plugin-owned custom table; callers own caching decisions.
		$row = $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is produced by Storage for a plugin-owned custom table.
				"SELECT * FROM {$table} WHERE dashboard_site_id = %d ORDER BY requested_at DESC, id DESC LIMIT 1",
				$site_id
			),
			ARRAY_A
		);

		return is_array( $row ) ? $row : null;
	}

	/**
	 * Gets recent action rows for one site.
	 *
	 * @since 0.1.15
	 *
	 * @param int $site_id Site ID.
	 * @param int $limit Limit.
	 * @return array<int,array<string,mixed>>
	 */
	public function recent_for_site( $site_id, $limit = 10 ) {
		global $wpdb;

		$site_id = absint( $site_id );
		$limit   = max( 1, min( 50, (int) $limit ) );

		if ( 0 === $site_id ) {
			return array();
		}

		$table = Alynt_Drime_Backups_Dashboard_Storage::actions_table();
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is produced by Storage for a plugin-owned custom table.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Repository reads a plugin-owned custom table; callers own caching decisions.
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, public_id, dashboard_site_id, action_type, state, requested_by, requested_at, accepted_at,
				completed_at, last_seen_at, retry_after_seconds, result_code, result_summary,
				client_state, client_result_code, client_result_summary, client_counts_json,
				client_updated_at, reconciled_at, redacted_context_json
				FROM {$table}
				WHERE dashboard_site_id = %d
				ORDER BY requested_at DESC, id DESC
				LIMIT %d",
				$site_id,
				$limit
			),
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Reads one action row by internal ID.
	 *
	 * @param int $action_id Action ID.
	 * @return array<string,mixed>|null
	 */
	private function row_by_id( $action_id ) {
		global $wpdb;

		$action_id = absint( $action_id );

		if ( 0 === $action_id ) {
			return null;
		}

		$table = Alynt_Drime_Backups_Dashboard_Storage::actions_table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Repository reads one plugin-owned custom-table row.
		$row = $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is produced by Storage for a plugin-owned custom table.
				"SELECT * FROM {$table} WHERE id = %d LIMIT 1",
				$action_id
			),
			ARRAY_A
		);

		return is_array( $row ) ? $row : null;
	}

	/**
	 * Decodes a redacted context JSON field.
	 *
	 * @param array<string,mixed> $row Action row.
	 * @return array<string,mixed>
	 */
	private function context_from_row( array $row ) {
		$context = ! empty( $row['redacted_context_json'] ) ? json_decode( (string) $row['redacted_context_json'], true, 16 ) : array();

		return is_array( $context ) ? $context : array();
	}
}
