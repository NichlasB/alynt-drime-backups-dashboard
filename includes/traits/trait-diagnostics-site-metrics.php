<?php
/**
 * Diagnostics site metrics helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds site count and recent polling diagnostics.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Site_Metrics {
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Backup_Source_Metrics;

	/**
	 * Builds site count diagnostics.
	 *
	 * @param array<int,array<string,mixed>> $sites Sites.
	 * @param array<int,array<string,mixed>> $snapshots Snapshots keyed by site ID.
	 * @param int                            $now Current Unix timestamp.
	 * @return array<string,mixed>
	 */
	private function count_diagnostics( array $sites, array $snapshots, $now ) {
		$counts = array(
			'total_sites'         => count( $sites ),
			'polling_ready'       => 0,
			'not_polling'         => 0,
			'due_now'             => 0,
			'missing_credentials' => 0,
			'paused'              => 0,
			'with_failures'       => 0,
			'statuses'            => array(),
			'record_states'       => array(
				'active'              => 0,
				'awaiting_first_poll' => 0,
				'pending'             => 0,
				'revoked'             => 0,
				'archived'            => 0,
				'other'               => 0,
				'unknown'             => 0,
			),
			'backup_sources'      => array(
				'reporting_sites'            => 0,
				'stale_sources'              => 0,
				'no_upload_evidence_sources' => 0,
				'not_configured_sources'     => 0,
			),
			'schedule_management' => array(
				'reporting_sites'                  => 0,
				'preview_only_sites'               => 0,
				'apply_sites'                      => 0,
				'unavailable_sites'                => 0,
				'reported_schedules'               => 0,
				'rollback_preview_supported_sites' => 0,
				'rollback_preview_hidden_sites'    => 0,
				'rollback_apply_advertised_sites'  => 0,
			),
			'cleanup_preview'     => array(
				'reporting_sites'                    => 0,
				'preview_supported_sites'            => 0,
				'unavailable_sites'                  => 0,
				'apply_or_mutation_advertised_sites' => 0,
				'supported_categories'               => 0,
			),
			'restore_readiness'   => array(
				'reporting_sites'       => 0,
				'evidence_sites'        => 0,
				'incomplete_sites'      => 0,
				'stale_sites'           => 0,
				'incompatible_sites'    => 0,
				'unknown_sites'         => 0,
				'reported_candidates'   => 0,
				'complete_candidates'   => 0,
				'incomplete_candidates' => 0,
			),
		);

		foreach ( $sites as $site ) {
			$site_id  = isset( $site['id'] ) ? (int) $site['id'] : 0;
			$snapshot = isset( $snapshots[ $site_id ] ) ? $snapshots[ $site_id ] : null;
			$status   = $this->classifier->classify( $site, $snapshot, $now );
			$category = isset( $status['category'] ) ? (string) $status['category'] : 'unknown';
			$state    = $this->site_record_state( $site );

			if ( ! isset( $counts['statuses'][ $category ] ) ) {
				$counts['statuses'][ $category ] = 0;
			}

			++$counts['statuses'][ $category ];

			if ( ! isset( $counts['record_states'][ $state ] ) ) {
				$counts['record_states'][ $state ] = 0;
			}

			++$counts['record_states'][ $state ];

			if ( ! empty( $site['archived_at'] ) ) {
				++$counts['not_polling'];
				continue;
			}

			if ( ! empty( $site['paused_at'] ) ) {
				++$counts['paused'];
			}

			if ( $this->is_polling_ready( $site ) ) {
				++$counts['polling_ready'];

				if ( $this->is_due_now( $site, $now ) ) {
					++$counts['due_now'];
				}
			} elseif ( $this->is_enrolled_for_polling( $site ) && empty( $site['paused_at'] ) && ! $this->has_polling_credentials( $site ) ) {
				++$counts['missing_credentials'];
			}

			if ( ! $this->is_polling_ready( $site ) ) {
				++$counts['not_polling'];
			}

			if ( ! empty( $site['consecutive_failures'] ) ) {
				++$counts['with_failures'];
			}

			$source_counts = $this->backup_source_diagnostics( $snapshot );

			foreach ( $source_counts as $key => $value ) {
				$counts['backup_sources'][ $key ] += $value;
			}

			$schedule_counts = $this->schedule_management_diagnostics( $snapshot );

			foreach ( $schedule_counts as $key => $value ) {
				$counts['schedule_management'][ $key ] += $value;
			}

			$cleanup_counts = $this->cleanup_preview_diagnostics( $snapshot );

			foreach ( $cleanup_counts as $key => $value ) {
				$counts['cleanup_preview'][ $key ] += $value;
			}

			$restore_readiness_counts = $this->restore_readiness_diagnostics( $snapshot );

			foreach ( $restore_readiness_counts as $key => $value ) {
				$counts['restore_readiness'][ $key ] += $value;
			}
		}

		ksort( $counts['statuses'] );

		return $counts;
	}

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

	/**
	 * Builds recent safe polling outcomes.
	 *
	 * @param array<int,array<string,mixed>> $sites Sites.
	 * @param int                            $limit Maximum rows.
	 * @return array<int,array<string,mixed>>
	 */
	private function recent_poll_outcomes( array $sites, $limit = 8 ) {
		$recent = array();

		foreach ( $sites as $site ) {
			if ( empty( $site['last_poll_attempt_at'] ) ) {
				continue;
			}

			$recent[] = array(
				'site_id'              => isset( $site['id'] ) ? (int) $site['id'] : 0,
				'site_label'           => $this->site_name( $site ),
				'enrollment_status'    => isset( $site['enrollment_status'] ) ? sanitize_key( $site['enrollment_status'] ) : '',
				'overall_status'       => isset( $site['overall_status'] ) ? sanitize_key( $site['overall_status'] ) : '',
				'last_poll_attempt_at' => (string) $site['last_poll_attempt_at'],
				'last_seen_at'         => isset( $site['last_seen_at'] ) ? (string) $site['last_seen_at'] : '',
				'next_poll_at'         => isset( $site['next_poll_at'] ) ? (string) $site['next_poll_at'] : '',
				'consecutive_failures' => isset( $site['consecutive_failures'] ) ? max( 0, (int) $site['consecutive_failures'] ) : 0,
				'last_error_code'      => isset( $site['last_error_code'] ) ? sanitize_key( $site['last_error_code'] ) : '',
				'last_error_summary'   => isset( $site['last_error_summary'] ) ? sanitize_text_field( $site['last_error_summary'] ) : '',
			);
		}

		usort(
			$recent,
			function ( $left, $right ) {
				return $this->timestamp( $right['last_poll_attempt_at'] ) <=> $this->timestamp( $left['last_poll_attempt_at'] );
			}
		);

		return array_slice( $recent, 0, max( 1, min( 50, (int) $limit ) ) );
	}
}
