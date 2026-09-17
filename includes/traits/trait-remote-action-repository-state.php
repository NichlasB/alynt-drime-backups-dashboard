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
Handles remote action state creation and mutation methods.
 *
 * @since 0.1.26
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_State {


	/**
	 * Records a queued dashboard action request.
	 *
	 * @since 0.1.15
	 *
	 * @param int                 $site_id Site ID.
	 * @param string              $action_type Action type.
	 * @param int                 $requested_by User ID.
	 * @param string              $idempotency_key Idempotency key.
	 * @param string              $action_key_id Action key ID.
	 * @param string              $expires_at Expiry date in MySQL UTC format.
	 * @param string              $request_fingerprint Request fingerprint.
	 * @param array<string,mixed> $context Redacted context.
	 * @param string              $public_id Optional pre-generated public action UUID.
	 * @return int|WP_Error
	 */
	public function create_request(
		$site_id,
		$action_type,
		$requested_by,
		$idempotency_key,
		$action_key_id,
		$expires_at,
		$request_fingerprint = '',
		array $context = array(),
		$public_id = ''
	) {
		global $wpdb;

		$site_id     = absint( $site_id );
		$action_type = $this->capabilities->sanitize_action_type( $action_type );
		$public_id   = $this->sanitize_uuid( $public_id );

		if ( 0 === $site_id || '' === $action_type ) {
			return new WP_Error( 'remote_action_invalid', __( 'The remote action request is not valid.', 'alynt-drime-backups-dashboard' ) );
		}

		$encoded_context = wp_json_encode( $this->redacted_context( $context ), JSON_UNESCAPED_SLASHES );

		if ( false === $encoded_context ) {
			return new WP_Error( 'remote_action_context_encode_failed', __( 'The remote action context could not be prepared for storage.', 'alynt-drime-backups-dashboard' ) );
		}

		$table = Alynt_Drime_Backups_Dashboard_Storage::actions_table();
		$now   = current_time( 'mysql', true );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Repository writes to a plugin-owned custom table.
		$inserted = $wpdb->insert(
			$table,
			array(
				'public_id'             => '' === $public_id ? $this->create_uuid() : $public_id,
				'dashboard_site_id'     => $site_id,
				'action_type'           => $action_type,
				'state'                 => self::DEFAULT_STATE,
				'idempotency_key'       => $this->bounded_identifier( $idempotency_key, 128 ),
				'action_key_id'         => $this->bounded_identifier( $action_key_id, 128 ),
				'requested_by'          => absint( $requested_by ),
				'requested_at'          => $now,
				'expires_at'            => $this->date_or_default( $expires_at, gmdate( 'Y-m-d H:i:s', time() + 300 ) ),
				'retry_after_seconds'   => 0,
				'result_code'           => '',
				'result_summary'        => '',
				'request_fingerprint'   => $this->sha256_or_empty( $request_fingerprint ),
				'redacted_context_json' => (string) $encoded_context,
				'created_at'            => $now,
				'updated_at'            => $now,
			),
			array( '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( false === $inserted || empty( $wpdb->insert_id ) ) {
			return new WP_Error( 'remote_action_store_failed', __( 'The dashboard could not store the remote action request.', 'alynt-drime-backups-dashboard' ) );
		}

		return (int) $wpdb->insert_id;
	}

	/**
	 * Marks an action state with a safe result summary.
	 *
	 * @since 0.1.15
	 *
	 * @param int    $action_id Action row ID.
	 * @param string $state Action state.
	 * @param string $result_code Result code.
	 * @param string $result_summary Result summary.
	 * @param int    $retry_after_seconds Retry-after seconds.
	 * @return bool
	 */
	public function mark_state( $action_id, $state, $result_code = '', $result_summary = '', $retry_after_seconds = 0 ) {
		global $wpdb;

		$action_id = absint( $action_id );

		if ( 0 === $action_id ) {
			return false;
		}

		$state = $this->capabilities->sanitize_state( $state );
		$now   = current_time( 'mysql', true );
		$data  = array(
			'state'               => $state,
			'retry_after_seconds' => max( 0, (int) $retry_after_seconds ),
			'result_code'         => sanitize_key( (string) $result_code ),
			'result_summary'      => $this->bounded_text( $result_summary, 240 ),
			'last_seen_at'        => $now,
			'updated_at'          => $now,
		);

		if ( 'accepted' === $state ) {
			$data['accepted_at'] = $now;
		}

		if ( in_array( $state, array( 'succeeded', 'failed', 'rejected', 'unsupported', 'rate_limited', 'busy', 'timed_out', 'stale', 'dispatch_failed' ), true ) ) {
			$data['completed_at'] = $now;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Repository updates a plugin-owned custom table; callers own caching decisions.
		$updated = $wpdb->update(
			Alynt_Drime_Backups_Dashboard_Storage::actions_table(),
			$data,
			array( 'id' => $action_id )
		);

		return false !== $updated;
	}

	/**
	 * Marks an action as dispatched without storing remote response contents.
	 *
	 * @since 0.1.15
	 *
	 * @param int $action_id Action row ID.
	 * @return bool
	 */
	public function mark_dispatched( $action_id ) {
		global $wpdb;

		$action_id = absint( $action_id );

		if ( 0 === $action_id ) {
			return false;
		}

		$now = current_time( 'mysql', true );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Repository updates a plugin-owned custom table; callers own caching decisions.
		$updated = $wpdb->update(
			Alynt_Drime_Backups_Dashboard_Storage::actions_table(),
			array(
				'dispatched_at' => $now,
				'updated_at'    => $now,
			),
			array( 'id' => $action_id )
		);

		return false !== $updated;
	}
}
