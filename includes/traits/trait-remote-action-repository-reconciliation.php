<?php
/**
 * Remote action repository reconciliation helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.26
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles client-reported action state reconciliation.
 *
 * @since 0.1.26
 */
trait Alynt_Drime_Backups_Dashboard_Remote_Action_Repository_Reconciliation {
	/**
	 * Stores a support-safe client-side action report on the matching dashboard action.
	 *
	 * @since 0.1.16
	 *
	 * @param int                 $action_id Dashboard action row ID.
	 * @param array<string,mixed> $client_action Sanitized client last-action summary.
	 * @param string|null         $now Current UTC MySQL timestamp.
	 * @return bool
	 */
	public function mark_client_report( $action_id, array $client_action, $now = null ) {
		global $wpdb;

		$action_id = absint( $action_id );
		$state     = $this->capabilities->sanitize_state( isset( $client_action['state'] ) ? (string) $client_action['state'] : '' );

		if ( 0 === $action_id ) {
			return false;
		}

		$now               = $this->date_or_default( null === $now ? gmdate( 'Y-m-d H:i:s' ) : $now, gmdate( 'Y-m-d H:i:s' ) );
		$client_updated_at = $this->client_action_updated_at( $client_action );
		$counts_json       = wp_json_encode( $this->client_action_counts( isset( $client_action['counts'] ) ? $client_action['counts'] : array() ), JSON_UNESCAPED_SLASHES );
		$context_json      = $this->merge_client_action_context_json( $action_id, $client_action );

		if ( false === $counts_json ) {
			$counts_json = '{}';
		}

		$data = array(
			'state'                 => $state,
			'client_state'          => $state,
			'client_result_code'    => isset( $client_action['result_code'] ) ? sanitize_key( (string) $client_action['result_code'] ) : '',
			'client_result_summary' => $this->bounded_text( isset( $client_action['result_summary'] ) ? (string) $client_action['result_summary'] : '', 240 ),
			'client_counts_json'    => (string) $counts_json,
			'client_updated_at'     => '' === $client_updated_at ? null : $client_updated_at,
			'reconciled_at'         => $now,
			'last_seen_at'          => $now,
			'updated_at'            => $now,
		);

		if ( '' !== $context_json ) {
			$data['redacted_context_json'] = $context_json;
		}

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
	 * Marks old accepted/running actions stale when status polling never confirms them.
	 *
	 * @since 0.1.16
	 *
	 * @param int         $site_id Dashboard site ID.
	 * @param string|null $now Current UTC MySQL timestamp.
	 * @return int|WP_Error
	 */
	public function mark_unconfirmed_actions_stale_for_site( $site_id, $now = null ) {
		global $wpdb;

		$site_id = absint( $site_id );

		if ( 0 === $site_id ) {
			return 0;
		}

		$now             = $this->date_or_default( null === $now ? gmdate( 'Y-m-d H:i:s' ) : $now, gmdate( 'Y-m-d H:i:s' ) );
		$now_timestamp   = strtotime( $now );
		$accepted_cutoff = gmdate( 'Y-m-d H:i:s', ( false === $now_timestamp ? time() : $now_timestamp ) - self::ACCEPTED_CONFIRMATION_STALE_SECONDS );
		$running_cutoff  = gmdate( 'Y-m-d H:i:s', ( false === $now_timestamp ? time() : $now_timestamp ) - self::RUNNING_CONFIRMATION_STALE_SECONDS );
		$summary         = __( 'No matching client-side action confirmation has appeared in status polling within the expected window.', 'alynt-drime-backups-dashboard' );
		$table           = Alynt_Drime_Backups_Dashboard_Storage::actions_table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Repository updates a plugin-owned custom table in a scoped maintenance pass.
		$updated = $wpdb->query(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name is produced by Storage for a plugin-owned custom table.
				"UPDATE {$table}
				SET state = 'stale',
					result_code = 'client_confirmation_stale',
					result_summary = %s,
					completed_at = %s,
					updated_at = %s
				WHERE dashboard_site_id = %d
					AND client_state IS NULL
					AND (
						( state = 'accepted' AND accepted_at IS NOT NULL AND accepted_at < %s )
						OR ( state = 'running' AND last_seen_at IS NOT NULL AND last_seen_at < %s )
					)",
				$summary,
				$now,
				$now,
				$site_id,
				$accepted_cutoff,
				$running_cutoff
			)
		);

		if ( false === $updated ) {
			return new WP_Error( 'remote_action_stale_update_failed', __( 'The dashboard could not update stale remote action records.', 'alynt-drime-backups-dashboard' ) );
		}

		return (int) $updated;
	}
}
