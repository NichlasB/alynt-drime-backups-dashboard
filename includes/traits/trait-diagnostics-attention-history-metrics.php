<?php
/**
 * Diagnostics attention-history metric helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.56
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds support-safe attention/recovery history diagnostics.
 *
 * @since 0.1.56
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Attention_History_Metrics {
	/**
	 * Builds support-safe aggregate attention/recovery history diagnostics.
	 *
	 * This intentionally reads only bounded, retained snapshot summary rows.
	 * It does not expose site labels, domains, raw payloads, credentials,
	 * paths, Drime identifiers, schedule IDs, or remote-action fingerprints.
	 *
	 * @param array<int,array<string,mixed>> $sites Sites.
	 * @return array<string,int>
	 */
	private function attention_history_diagnostics( array $sites ) {
		$counts = array(
			'records_with_history'         => 0,
			'recently_recovered_records'   => 0,
			'repeated_attention_records'   => 0,
			'recent_attention_transitions' => 0,
		);

		foreach ( $sites as $site ) {
			if ( ! empty( $site['archived_at'] ) ) {
				continue;
			}

			$site_id = isset( $site['id'] ) ? absint( $site['id'] ) : 0;

			if ( 0 === $site_id ) {
				continue;
			}

			$history = $this->snapshots->recent_for_site( $site_id, 10 );

			if ( count( $history ) < 2 ) {
				continue;
			}

			++$counts['records_with_history'];

			$latest_status   = $this->snapshot_overall_status( $history[0] );
			$previous_status = $this->snapshot_overall_status( $history[1] );

			if ( 'working' === $latest_status && $this->is_attention_history_status( $previous_status ) ) {
				++$counts['recently_recovered_records'];
			}

			$transitions                             = $this->attention_transition_count( $history );
			$counts['recent_attention_transitions'] += $transitions;

			if ( $transitions > 1 ) {
				++$counts['repeated_attention_records'];
			}
		}

		return $counts;
	}

	/**
	 * Gets a support-safe summary code for attention/recovery aggregates.
	 *
	 * @param array<string,mixed> $attention_history Attention history counts.
	 * @return string
	 */
	private function attention_history_summary_code( array $attention_history ) {
		$records_with_history = isset( $attention_history['records_with_history'] ) ? max( 0, (int) $attention_history['records_with_history'] ) : 0;
		$repeated_records     = isset( $attention_history['repeated_attention_records'] ) ? max( 0, (int) $attention_history['repeated_attention_records'] ) : 0;
		$recovered_records    = isset( $attention_history['recently_recovered_records'] ) ? max( 0, (int) $attention_history['recently_recovered_records'] ) : 0;
		$transitions          = isset( $attention_history['recent_attention_transitions'] ) ? max( 0, (int) $attention_history['recent_attention_transitions'] ) : 0;

		if ( 0 === $records_with_history ) {
			return 'no_retained_history';
		}

		if ( $repeated_records > 0 ) {
			return 'repeated_attention_seen';
		}

		if ( $recovered_records > 0 ) {
			return 'recent_recoveries_seen';
		}

		if ( $transitions > 0 ) {
			return 'attention_transitions_seen';
		}

		return 'quiet_retained_history';
	}

	/**
	 * Counts transitions from a non-attention state into an attention state.
	 *
	 * Snapshot repository history is newest-first; transition counting is easier
	 * and less error-prone from oldest to newest.
	 *
	 * @param array<int,array<string,mixed>> $history Recent snapshots.
	 * @return int
	 */
	private function attention_transition_count( array $history ) {
		$ordered     = array_reverse( $history );
		$count       = 0;
		$was_problem = null;

		foreach ( $ordered as $snapshot ) {
			$is_problem = $this->is_attention_history_status( $this->snapshot_overall_status( $snapshot ) );

			if ( true === $is_problem && false === $was_problem ) {
				++$count;
			}

			$was_problem = $is_problem;
		}

		return $count;
	}

	/**
	 * Gets a normalized snapshot status for aggregate transition checks.
	 *
	 * @param array<string,mixed> $snapshot Snapshot row.
	 * @return string
	 */
	private function snapshot_overall_status( array $snapshot ) {
		return isset( $snapshot['overall_status'] ) ? sanitize_key( $snapshot['overall_status'] ) : '';
	}

	/**
	 * Determines whether a retained status is an attention/problem state.
	 *
	 * Pending and paused states are intentionally excluded because they do not
	 * necessarily describe a backup-health incident.
	 *
	 * @param string $status Snapshot overall status.
	 * @return bool
	 */
	private function is_attention_history_status( $status ) {
		return in_array(
			(string) $status,
			array(
				'needs_attention',
				'not_reporting',
				'incompatible',
				'not_configured',
			),
			true
		);
	}
}
