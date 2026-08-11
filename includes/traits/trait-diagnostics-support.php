<?php
/**
 * Diagnostics support summary helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds support-safe diagnostics summaries.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Support {
	/**
	 * Builds a redacted support-copy summary.
	 *
	 * This intentionally omits site labels, domains, credentials, authorization
	 * headers, raw response bodies, raw payload JSON, and local paths.
	 *
	 * @since 0.1.0
	 *
	 * @param array<int,array<string,mixed>>      $sites Sites.
	 * @param array<int,array<string,mixed>>|null $snapshots Snapshots keyed by site ID.
	 * @param int|null                            $now Current Unix timestamp.
	 * @return array<string,mixed>
	 */
	public function support_summary( $sites = null, $snapshots = null, $now = null ) {
		if ( null === $sites ) {
			$sites     = $this->sites->all();
			$snapshots = $this->snapshots->latest_by_site_ids( $this->site_ids( $sites ) );
		}

		if ( ! is_array( $sites ) ) {
			$sites = array();
		}

		if ( ! is_array( $snapshots ) ) {
			$snapshots = array();
		}

		$now       = null === $now ? time() : (int) $now;
		$scheduler = $this->scheduler_diagnostics( $now );
		$counts    = $this->count_diagnostics( $sites, $snapshots, $now );
		$recent    = $this->recent_poll_outcomes( $sites, 10 );
		$logging   = array(
			'settings' => $this->event_log->settings(),
			'summary'  => $this->event_log->summary(),
		);

		return $this->support_summary_from_diagnostics( $scheduler, $counts, $recent, $logging, $now );
	}

	/**
	 * Builds support-safe diagnostics from already-collected sections.
	 *
	 * @param array<string,mixed>            $scheduler Scheduler diagnostics.
	 * @param array<string,mixed>            $counts Site counts.
	 * @param array<int,array<string,mixed>> $recent Recent outcomes.
	 * @param array<string,mixed>            $logging Logging diagnostics.
	 * @param int                            $now Current Unix timestamp.
	 * @return array<string,mixed>
	 */
	private function support_summary_from_diagnostics( array $scheduler, array $counts, array $recent, array $logging, $now ) {
		$now = (int) $now;

		return array(
			'plugin'      => array(
				'name'    => 'Alynt Drime Backups Dashboard',
				'version' => defined( 'ALYNT_DRIME_BACKUPS_DASHBOARD_VERSION' ) ? ALYNT_DRIME_BACKUPS_DASHBOARD_VERSION : '',
			),
			'generated'   => array(
				'current_utc' => gmdate( 'Y-m-d H:i:s', $now ),
			),
			'scheduler'   => array(
				'poll_schedule_state' => $scheduler['poll_schedule_state'],
				'cleanup_state'       => $scheduler['cleanup_state'],
				'poll_interval'       => $scheduler['poll_interval_seconds'],
				'batch_size'          => $scheduler['poll_batch_size'],
				'retention_days'      => $scheduler['retention_days'],
				'global_lock_active'  => $scheduler['global_lock_active'],
			),
			'counts'      => $counts,
			'logging'     => $this->support_logging_summary_from_diagnostics( $logging ),
			'recent_safe' => $this->support_recent_outcomes( $recent ),
		);
	}

	/**
	 * Builds support-safe logging summary without event context.
	 *
	 * @return array<string,mixed>
	 */
	private function support_logging_summary() {
		return $this->support_logging_summary_from_diagnostics(
			array(
				'settings' => $this->event_log->settings(),
				'summary'  => $this->event_log->summary(),
			)
		);
	}

	/**
	 * Builds support-safe logging summary from collected diagnostics.
	 *
	 * @param array<string,mixed> $logging Logging diagnostics.
	 * @return array<string,mixed>
	 */
	private function support_logging_summary_from_diagnostics( array $logging ) {
		$settings = isset( $logging['settings'] ) && is_array( $logging['settings'] ) ? $logging['settings'] : array();
		$summary  = isset( $logging['summary'] ) && is_array( $logging['summary'] ) ? $logging['summary'] : array();

		return array(
			'enabled'        => ! empty( $settings['enabled'] ),
			'minimum_level'  => isset( $settings['minimum_level'] ) ? (string) $settings['minimum_level'] : 'warning',
			'retention_days' => isset( $settings['retention_days'] ) ? (int) $settings['retention_days'] : 14,
			'event_count'    => isset( $summary['total'] ) ? (int) $summary['total'] : 0,
			'last_event_at'  => isset( $summary['last_event_at'] ) ? (string) $summary['last_event_at'] : '',
		);
	}

	/**
	 * Reduces recent outcomes to support-safe fields.
	 *
	 * @param array<int,array<string,mixed>> $recent Recent outcomes.
	 * @return array<int,array<string,mixed>>
	 */
	private function support_recent_outcomes( array $recent ) {
		$safe = array();

		foreach ( $recent as $row ) {
			$safe[] = array(
				'site_id'              => isset( $row['site_id'] ) ? (int) $row['site_id'] : 0,
				'enrollment_status'    => isset( $row['enrollment_status'] ) ? sanitize_key( $row['enrollment_status'] ) : '',
				'overall_status'       => isset( $row['overall_status'] ) ? sanitize_key( $row['overall_status'] ) : '',
				'last_poll_attempt_at' => isset( $row['last_poll_attempt_at'] ) ? (string) $row['last_poll_attempt_at'] : '',
				'next_poll_at'         => isset( $row['next_poll_at'] ) ? (string) $row['next_poll_at'] : '',
				'consecutive_failures' => isset( $row['consecutive_failures'] ) ? max( 0, (int) $row['consecutive_failures'] ) : 0,
				'last_error_code'      => isset( $row['last_error_code'] ) ? sanitize_key( $row['last_error_code'] ) : '',
			);
		}

		return $safe;
	}
}
