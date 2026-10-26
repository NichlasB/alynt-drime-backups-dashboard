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

	use Alynt_Drime_Backups_Dashboard_Diagnostics_Support_Sections;

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

		$now                         = null === $now ? time() : (int) $now;
		$scheduler                   = $this->scheduler_diagnostics( $now );
		$counts                      = $this->count_diagnostics( $sites, $snapshots, $now );
		$counts['attention_history'] = $this->attention_history_diagnostics( $sites );
		$recent                      = $this->recent_poll_outcomes( $sites, 10 );
		$logging                     = array(
			'settings' => $this->event_log->settings(),
			'summary'  => $this->event_log->summary(),
			'audit'    => array(
				'summary' => $this->event_log->audit_summary(),
			),
		);

		$summaries = array(
			'attention_history' => $this->attention_history_summary_code( $counts['attention_history'] ),
		);

		return $this->support_summary_from_diagnostics( $scheduler, $counts, $recent, $logging, $now, $this->remote_actions->support_summary(), $summaries );
	}

	/**
	 * Builds support-safe diagnostics from already-collected sections.
	 *
	 * @param array<string,mixed>            $scheduler Scheduler diagnostics.
	 * @param array<string,mixed>            $counts Site counts.
	 * @param array<int,array<string,mixed>> $recent Recent outcomes.
	 * @param array<string,mixed>            $logging Logging diagnostics.
	 * @param int                            $now Current Unix timestamp.
	 * @param array<string,mixed>|null       $remote_actions Remote action aggregate summary.
	 * @param array<string,string>           $summaries Diagnostic summary codes.
	 * @return array<string,mixed>
	 */
	private function support_summary_from_diagnostics( array $scheduler, array $counts, array $recent, array $logging, $now, $remote_actions = null, array $summaries = array() ) {
		$now            = (int) $now;
		$remote_actions = is_array( $remote_actions ) ? $remote_actions : array();

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
			'summaries'   => $this->support_diagnostic_summaries( $summaries ),
			'logging'     => $this->support_logging_summary_from_diagnostics( $logging ),
			'actions'     => $this->support_remote_action_summary( $remote_actions ),
			'recent_safe' => $this->support_recent_outcomes( $recent ),
		);
	}
}
