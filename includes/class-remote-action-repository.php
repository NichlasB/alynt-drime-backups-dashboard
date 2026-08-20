<?php
/**
 * Remote action repository.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.15
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stores dashboard-owned remote action request records.
 *
 * @since 0.1.15
 */
class Alynt_Drime_Backups_Dashboard_Remote_Action_Repository {
	const DEFAULT_STATE  = 'queued_for_dispatch';
	const RETENTION_DAYS = 90;

	/**
	 * Capability sanitizer.
	 *
	 * @var Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities
	 */
	private $capabilities;

	/**
	 * Constructor.
	 *
	 * @since 0.1.15
	 *
	 * @param Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities|null $capabilities Capability sanitizer.
	 */
	public function __construct( $capabilities = null ) {
		$this->capabilities = $capabilities instanceof Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities ? $capabilities : new Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities();
	}

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
		array $context = array()
	) {
		global $wpdb;

		$site_id     = absint( $site_id );
		$action_type = $this->capabilities->sanitize_action_type( $action_type );

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
				'public_id'             => $this->create_uuid(),
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
				completed_at, last_seen_at, retry_after_seconds, result_code, result_summary
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
	 * Deletes old completed action records in bounded batches.
	 *
	 * @since 0.1.15
	 *
	 * @param int $retention_days Retention days.
	 * @param int $batch_size Batch size.
	 * @return int|WP_Error
	 */
	public function cleanup_retention( $retention_days = self::RETENTION_DAYS, $batch_size = 500 ) {
		global $wpdb;

		$retention_days = max( 1, min( 365, (int) $retention_days ) );
		$batch_size     = max( 1, min( 5000, (int) $batch_size ) );
		$cutoff         = gmdate( 'Y-m-d H:i:s', time() - ( $retention_days * 86400 ) );
		$table          = Alynt_Drime_Backups_Dashboard_Storage::actions_table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Repository deletes from a plugin-owned custom table in bounded batches.
		$deleted = $wpdb->query(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is produced by Storage for a plugin-owned custom table.
				"DELETE FROM {$table}
				WHERE completed_at IS NOT NULL
					AND completed_at < %s
				ORDER BY completed_at ASC, id ASC
				LIMIT %d",
				$cutoff,
				$batch_size
			)
		);

		if ( false === $deleted ) {
			return new WP_Error( 'remote_action_cleanup_failed', __( 'The dashboard could not clean up old remote action records.', 'alynt-drime-backups-dashboard' ) );
		}

		return (int) $deleted;
	}

	/**
	 * Redacts context before local storage.
	 *
	 * @param array<string,mixed> $context Context.
	 * @return array<string,mixed>
	 */
	private function redacted_context( array $context ) {
		$clean = array();

		foreach ( $context as $key => $value ) {
			$key = sanitize_key( (string) $key );

			if ( '' === $key ) {
				continue;
			}

			if (
				preg_match(
					'/(secret|token|credential|password|authorization|cookie|nonce|signature|private|path|file|package|drime|url|sql)/',
					$key
				)
			) {
				$clean[ $key ] = '[redacted]';
				continue;
			}

			if ( is_bool( $value ) ) {
				$clean[ $key ] = $value;
			} elseif ( is_int( $value ) || is_float( $value ) ) {
				$clean[ $key ] = max( 0, (int) $value );
			} elseif ( is_scalar( $value ) ) {
				$clean[ $key ] = $this->bounded_text( (string) $value, 120 );
			}
		}

		return $clean;
	}

	/**
	 * Creates a UUID.
	 *
	 * @return string
	 */
	private function create_uuid() {
		$bytes    = random_bytes( 16 );
		$bytes[6] = chr( ( ord( $bytes[6] ) & 0x0f ) | 0x40 );
		$bytes[8] = chr( ( ord( $bytes[8] ) & 0x3f ) | 0x80 );
		$hex      = bin2hex( $bytes );

		return sprintf(
			'%s-%s-%s-%s-%s',
			substr( $hex, 0, 8 ),
			substr( $hex, 8, 4 ),
			substr( $hex, 12, 4 ),
			substr( $hex, 16, 4 ),
			substr( $hex, 20, 12 )
		);
	}

	/**
	 * Sanitizes an identifier.
	 *
	 * @param string $value Raw value.
	 * @param int    $max_length Max length.
	 * @return string
	 */
	private function bounded_identifier( $value, $max_length ) {
		return substr( preg_replace( '/[^A-Za-z0-9_\-\.]/', '', (string) $value ), 0, max( 1, (int) $max_length ) );
	}

	/**
	 * Sanitizes and bounds text.
	 *
	 * @param string $value Raw value.
	 * @param int    $max_length Max length.
	 * @return string
	 */
	private function bounded_text( $value, $max_length ) {
		$value      = sanitize_text_field( (string) $value );
		$max_length = max( 1, (int) $max_length );

		if ( function_exists( 'mb_substr' ) ) {
			return mb_substr( $value, 0, $max_length );
		}

		return substr( $value, 0, $max_length );
	}

	/**
	 * Keeps valid SHA-256 fingerprints only.
	 *
	 * @param string $value Value.
	 * @return string
	 */
	private function sha256_or_empty( $value ) {
		return preg_match( '/^[a-f0-9]{64}$/', (string) $value ) ? (string) $value : '';
	}

	/**
	 * Uses a date value or fallback.
	 *
	 * @param string $date Date.
	 * @param string $fallback Fallback.
	 * @return string
	 */
	private function date_or_default( $date, $fallback ) {
		$timestamp = strtotime( (string) $date );

		return false === $timestamp ? $fallback : gmdate( 'Y-m-d H:i:s', $timestamp );
	}
}
