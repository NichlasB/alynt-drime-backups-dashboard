<?php
/**
 * Admin page diagnostics overview.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the diagnostics screen shell and summary panels.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Diagnostics_Overview {
	/**
	 * Renders redacted diagnostics.
	 *
	 * @return void
	 */
	private function render_diagnostics_shell() {
		$diagnostics       = $this->diagnostics->collect();
		$scheduler         = isset( $diagnostics['scheduler'] ) && is_array( $diagnostics['scheduler'] ) ? $diagnostics['scheduler'] : array();
		$counts            = isset( $diagnostics['counts'] ) && is_array( $diagnostics['counts'] ) ? $diagnostics['counts'] : array();
		$recent            = isset( $diagnostics['recent'] ) && is_array( $diagnostics['recent'] ) ? $diagnostics['recent'] : array();
		$logging           = isset( $diagnostics['logging'] ) && is_array( $diagnostics['logging'] ) ? $diagnostics['logging'] : array();
		$support           = isset( $diagnostics['support'] ) && is_array( $diagnostics['support'] ) ? $diagnostics['support'] : array();
		$states            = isset( $counts['record_states'] ) && is_array( $counts['record_states'] ) ? $counts['record_states'] : array();
		$attention_history = isset( $counts['attention_history'] ) && is_array( $counts['attention_history'] ) ? $counts['attention_history'] : array();
		$restore_readiness = isset( $counts['restore_readiness'] ) && is_array( $counts['restore_readiness'] ) ? $counts['restore_readiness'] : array();
		$summaries         = isset( $diagnostics['summaries'] ) && is_array( $diagnostics['summaries'] ) ? $diagnostics['summaries'] : array();

		echo '<section aria-labelledby="adbd-diagnostics-heading">';
		echo '<h2 id="adbd-diagnostics-heading">' . esc_html__( 'Diagnostics', 'alynt-drime-backups-dashboard' ) . '</h2>';
		echo '<p class="adbd-screen-intro">' . esc_html__( 'Redacted scheduler, retention, and polling evidence for operators. This screen never displays pairing tokens, polling secrets, authorization headers, raw response bodies, filesystem paths, SQL, cookies, nonces, salts, or Drime credentials.', 'alynt-drime-backups-dashboard' ) . '</p>';
		$this->render_diagnostics_freshness_notice( $scheduler );
		$this->render_diagnostics_runtime_identity();

		$this->render_diagnostics_settings( $logging );

		echo '<div class="adbd-panel-grid"><div class="adbd-panel"><h3>' . esc_html__( 'Scheduler', 'alynt-drime-backups-dashboard' ) . '</h3>';
		echo '<table class="widefat striped adbd-detail-table" aria-label="' . esc_attr__( 'Scheduler diagnostics', 'alynt-drime-backups-dashboard' ) . '"><tbody>';
		$this->render_detail_row( __( 'Poll hook', 'alynt-drime-backups-dashboard' ), $this->diagnostic_value( $scheduler, 'poll_hook' ) );
		$this->render_detail_row( __( 'Poll schedule state', 'alynt-drime-backups-dashboard' ), $this->diagnostic_value( $scheduler, 'poll_schedule_state' ) );
		$this->render_detail_row( __( 'Next scheduled poll', 'alynt-drime-backups-dashboard' ), $this->date_or_dash( $this->diagnostic_value( $scheduler, 'poll_next_at' ) ) );
		$this->render_detail_row( __( 'Poll interval', 'alynt-drime-backups-dashboard' ), $this->seconds_label( $this->diagnostic_int( $scheduler, 'poll_interval_seconds' ) ) );
		$this->render_detail_row( __( 'Batch size', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $scheduler, 'poll_batch_size' ) );
		$this->render_detail_row( __( 'Stale threshold', 'alynt-drime-backups-dashboard' ), $this->seconds_label( $this->diagnostic_int( $scheduler, 'stale_after_seconds' ) ) );
		$this->render_detail_row( __( 'Global scheduler lock', 'alynt-drime-backups-dashboard' ), $this->lock_label( isset( $scheduler['global_lock_active'] ) ? $scheduler['global_lock_active'] : null ) );
		$this->render_detail_row( __( 'Current UTC time', 'alynt-drime-backups-dashboard' ), $this->diagnostic_value( $scheduler, 'current_utc' ) );
		echo '</tbody></table></div>';

		echo '<div class="adbd-panel"><h3>' . esc_html__( 'History Retention', 'alynt-drime-backups-dashboard' ) . '</h3>';
		echo '<table class="widefat striped adbd-detail-table" aria-label="' . esc_attr__( 'History retention diagnostics', 'alynt-drime-backups-dashboard' ) . '"><tbody>';
		$this->render_detail_row( __( 'Cleanup hook', 'alynt-drime-backups-dashboard' ), $this->diagnostic_value( $scheduler, 'cleanup_hook' ) );
		$this->render_detail_row( __( 'Cleanup schedule state', 'alynt-drime-backups-dashboard' ), $this->diagnostic_value( $scheduler, 'cleanup_state' ) );
		$this->render_detail_row( __( 'Next cleanup', 'alynt-drime-backups-dashboard' ), $this->date_or_dash( $this->diagnostic_value( $scheduler, 'cleanup_next_at' ) ) );
		$this->render_detail_row( __( 'Retention window', 'alynt-drime-backups-dashboard' ), sprintf( /* translators: %d: retention days. */ __( '%d days', 'alynt-drime-backups-dashboard' ), $this->diagnostic_int( $scheduler, 'retention_days' ) ) );
		$this->render_detail_row( __( 'Cleanup batch size', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $scheduler, 'cleanup_batch_size' ) );
		echo '</tbody></table></div>';

		echo '<div class="adbd-panel"><h3>' . esc_html__( 'Site Polling Summary', 'alynt-drime-backups-dashboard' ) . '</h3>';
		echo '<table class="widefat striped adbd-detail-table" aria-label="' . esc_attr__( 'Site polling summary diagnostics', 'alynt-drime-backups-dashboard' ) . '"><tbody>';
		$this->render_detail_row( __( 'Total dashboard records', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $counts, 'total_sites' ) );
		$this->render_detail_row( __( 'Ready for scheduled polling', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $counts, 'polling_ready' ) );
		$this->render_detail_row( __( 'Records not currently polling', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $counts, 'not_polling' ) );
		$this->render_detail_row( __( 'Due now', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $counts, 'due_now' ) );
		$this->render_detail_row( __( 'Missing polling credentials', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $counts, 'missing_credentials' ) );
		$this->render_detail_row( __( 'Paused locally', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $counts, 'paused' ) );
		$this->render_detail_row( __( 'Active enrollment records', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $states, 'active' ) );
		$this->render_detail_row( __( 'Awaiting first poll records', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $states, 'awaiting_first_poll' ) );
		$this->render_detail_row( __( 'Pending pairing records', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $states, 'pending' ) );
		$this->render_detail_row( __( 'Locally revoked records', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $states, 'revoked' ) );
		$this->render_detail_row( __( 'Archived local records', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $states, 'archived' ) );
		$this->render_detail_row( __( 'Other or unknown enrollment records', 'alynt-drime-backups-dashboard' ), (string) ( $this->diagnostic_int( $states, 'other' ) + $this->diagnostic_int( $states, 'unknown' ) ) );
		$this->render_detail_row( __( 'Sites with recorded failures', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $counts, 'with_failures' ) );
		echo '</tbody></table></div></div>';

		echo '<div class="adbd-panel"><h3>' . esc_html__( 'Attention / Recovery History', 'alynt-drime-backups-dashboard' ) . '</h3>';
		echo '<table class="widefat striped adbd-detail-table" aria-label="' . esc_attr__( 'Attention and recovery history diagnostics', 'alynt-drime-backups-dashboard' ) . '"><tbody>';
		$this->render_detail_row( __( 'Summary', 'alynt-drime-backups-dashboard' ), $this->attention_history_summary_label( isset( $summaries['attention_history'] ) ? $summaries['attention_history'] : '' ) );
		$this->render_detail_row( __( 'Records with retained history', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $attention_history, 'records_with_history' ) );
		$this->render_detail_row( __( 'Recently recovered records', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $attention_history, 'recently_recovered_records' ) );
		$this->render_detail_row( __( 'Repeated attention records', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $attention_history, 'repeated_attention_records' ) );
		$this->render_detail_row( __( 'Recent attention transitions', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $attention_history, 'recent_attention_transitions' ) );
		echo '</tbody></table>';
		echo '<p class="description">' . esc_html__( 'These aggregate counts come from retained redacted snapshot status history only. They do not list client domains, labels, raw payloads, paths, credentials, Drime identifiers, or remote-action details.', 'alynt-drime-backups-dashboard' ) . '</p></div>';

		echo '<div class="adbd-panel"><h3>' . esc_html__( 'Restore Readiness Evidence', 'alynt-drime-backups-dashboard' ) . '</h3>';
		echo '<table class="widefat striped adbd-detail-table" aria-label="' . esc_attr__( 'Restore readiness evidence diagnostics', 'alynt-drime-backups-dashboard' ) . '"><tbody>';
		$this->render_detail_row( __( 'Sites reporting evidence', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $restore_readiness, 'reporting_sites' ) );
		$this->render_detail_row( __( 'Sites with evidence available', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $restore_readiness, 'evidence_sites' ) );
		$this->render_detail_row( __( 'Sites with incomplete evidence', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $restore_readiness, 'incomplete_sites' ) );
		$this->render_detail_row( __( 'Sites with stale evidence', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $restore_readiness, 'stale_sites' ) );
		$this->render_detail_row( __( 'Sites with incompatible evidence', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $restore_readiness, 'incompatible_sites' ) );
		$this->render_detail_row( __( 'Unknown evidence states', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $restore_readiness, 'unknown_sites' ) );
		$this->render_detail_row( __( 'Reported source candidates', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $restore_readiness, 'reported_candidates' ) );
		$this->render_detail_row( __( 'Complete source candidates', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $restore_readiness, 'complete_candidates' ) );
		$this->render_detail_row( __( 'Incomplete source candidates', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $restore_readiness, 'incomplete_candidates' ) );
		echo '</tbody></table>';
		echo '<p class="description">' . esc_html__( 'These support-safe counts summarize optional restore-readiness evidence from latest client reports. They do not expose candidate references, paths, filenames, package names, Drime identifiers, credentials, or restore controls, and they are not a restore guarantee.', 'alynt-drime-backups-dashboard' ) . '</p></div>';

		$this->render_status_count_table( isset( $counts['statuses'] ) && is_array( $counts['statuses'] ) ? $counts['statuses'] : array() );
		$this->render_recent_poll_outcomes( $recent );
		$this->render_event_log_diagnostics( $logging );
		$this->render_support_copy_output( $support );
		echo '</section>';
	}

	/**
	 * Renders the visible dashboard runtime identity panel.
	 *
	 * @return void
	 */
	private function render_diagnostics_runtime_identity() {
		$version               = defined( 'ALYNT_DRIME_BACKUPS_DASHBOARD_VERSION' ) ? ALYNT_DRIME_BACKUPS_DASHBOARD_VERSION : '';
		$protocol_version      = class_exists( 'Alynt_Drime_Backups_Dashboard_Enrollment_REST_Controller' ) ? (string) Alynt_Drime_Backups_Dashboard_Enrollment_REST_Controller::PROTOCOL_VERSION : '1';
		$status_schema_version = class_exists( 'Alynt_Drime_Backups_Dashboard_Enrollment_REST_Controller' ) ? (string) Alynt_Drime_Backups_Dashboard_Enrollment_REST_Controller::STATUS_SCHEMA_VERSION : '1';

		echo '<div class="adbd-panel adbd-runtime-panel"><h3>' . esc_html__( 'Dashboard Runtime', 'alynt-drime-backups-dashboard' ) . '</h3>';
		echo '<table class="widefat striped adbd-detail-table" aria-label="' . esc_attr__( 'Dashboard runtime diagnostics', 'alynt-drime-backups-dashboard' ) . '"><tbody>';
		$this->render_detail_row( __( 'Plugin', 'alynt-drime-backups-dashboard' ), __( 'Alynt Drime Backups Dashboard', 'alynt-drime-backups-dashboard' ) );
		$this->render_detail_row( __( 'Installed version', 'alynt-drime-backups-dashboard' ), '' !== $version ? $version : '-' );
		$this->render_detail_row(
			__( 'Polling contract', 'alynt-drime-backups-dashboard' ),
			sprintf(
				/* translators: 1: protocol version. 2: status schema version. */
				__( 'Protocol v%1$s / Status schema v%2$s', 'alynt-drime-backups-dashboard' ),
				$protocol_version,
				$status_schema_version
			)
		);
		echo '</tbody></table>';
		echo '<p class="description">' . esc_html__( 'This support-safe identity check helps confirm which dashboard build generated the Diagnostics view without exposing site labels, domains, paths, credentials, tokens, raw payloads, or response bodies.', 'alynt-drime-backups-dashboard' ) . '</p></div>';
	}

	/**
	 * Gets an operator-facing summary label for attention/recovery aggregates.
	 *
	 * @param string $code Summary code.
	 * @return string
	 */
	private function attention_history_summary_label( $code ) {
		switch ( (string) $code ) {
			case 'no_retained_history':
				return __( 'No retained history yet', 'alynt-drime-backups-dashboard' );
			case 'quiet_retained_history':
				return __( 'No recent attention transitions', 'alynt-drime-backups-dashboard' );
			case 'recent_recoveries_seen':
				return __( 'Recent recoveries seen', 'alynt-drime-backups-dashboard' );
			case 'repeated_attention_seen':
				return __( 'Repeated attention seen', 'alynt-drime-backups-dashboard' );
			case 'attention_transitions_seen':
				return __( 'Attention transitions seen', 'alynt-drime-backups-dashboard' );
			default:
				return __( 'Not enough history to summarize', 'alynt-drime-backups-dashboard' );
		}
	}

	/**
	 * Renders generated-at copy and a cache-busted Diagnostics refresh link.
	 *
	 * @param array<string,mixed> $scheduler Scheduler diagnostics.
	 * @return void
	 */
	private function render_diagnostics_freshness_notice( array $scheduler ) {
		$current_utc = $this->diagnostic_value( $scheduler, 'current_utc' );
		$refresh_url = $this->diagnostics_refresh_url( $current_utc );

		echo '<p class="description adbd-diagnostics-freshness">';

		if ( '' !== $current_utc ) {
			printf(
				/* translators: %s: UTC diagnostics generation time. */
				esc_html__( 'Generated at %s UTC.', 'alynt-drime-backups-dashboard' ),
				esc_html( $current_utc )
			);
			echo ' ';
		}

		printf(
			'<a href="%1$s">%2$s</a>',
			esc_url( $refresh_url ),
			esc_html__( 'Refresh diagnostics', 'alynt-drime-backups-dashboard' )
		);

		echo '</p>';
	}

	/**
	 * Builds a cache-busted Diagnostics URL.
	 *
	 * @param string $current_utc Current UTC timestamp.
	 * @return string
	 */
	private function diagnostics_refresh_url( $current_utc ) {
		$cache_buster = '' !== $current_utc ? preg_replace( '/[^0-9]/', '', $current_utc ) : (string) time();

		return add_query_arg(
			array(
				'page'        => self::MENU_SLUG,
				'tab'         => 'diagnostics',
				'_adbd_check' => $cache_buster,
			),
			admin_url( 'tools.php' )
		);
	}
}
