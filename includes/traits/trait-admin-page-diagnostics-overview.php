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
		$diagnostics = $this->diagnostics->collect();
		$scheduler   = isset( $diagnostics['scheduler'] ) && is_array( $diagnostics['scheduler'] ) ? $diagnostics['scheduler'] : array();
		$counts      = isset( $diagnostics['counts'] ) && is_array( $diagnostics['counts'] ) ? $diagnostics['counts'] : array();
		$recent      = isset( $diagnostics['recent'] ) && is_array( $diagnostics['recent'] ) ? $diagnostics['recent'] : array();
		$logging     = isset( $diagnostics['logging'] ) && is_array( $diagnostics['logging'] ) ? $diagnostics['logging'] : array();
		$support     = isset( $diagnostics['support'] ) && is_array( $diagnostics['support'] ) ? $diagnostics['support'] : array();
		$states      = isset( $counts['record_states'] ) && is_array( $counts['record_states'] ) ? $counts['record_states'] : array();

		echo '<section aria-labelledby="adbd-diagnostics-heading">';
		echo '<h2 id="adbd-diagnostics-heading">' . esc_html__( 'Diagnostics', 'alynt-drime-backups-dashboard' ) . '</h2>';
		echo '<p class="adbd-screen-intro">' . esc_html__( 'Redacted scheduler, retention, and polling evidence for operators. This screen never displays pairing tokens, polling secrets, authorization headers, raw response bodies, filesystem paths, SQL, cookies, nonces, salts, or Drime credentials.', 'alynt-drime-backups-dashboard' ) . '</p>';
		$this->render_diagnostics_freshness_notice( $scheduler );

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

		$this->render_status_count_table( isset( $counts['statuses'] ) && is_array( $counts['statuses'] ) ? $counts['statuses'] : array() );
		$this->render_recent_poll_outcomes( $recent );
		$this->render_event_log_diagnostics( $logging );
		$this->render_support_copy_output( $support );
		echo '</section>';
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
