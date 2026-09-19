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
				'reporting_sites'    => 0,
				'preview_only_sites' => 0,
				'apply_sites'        => 0,
				'unavailable_sites'  => 0,
				'reported_schedules' => 0,
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
		}

		ksort( $counts['statuses'] );

		return $counts;
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
