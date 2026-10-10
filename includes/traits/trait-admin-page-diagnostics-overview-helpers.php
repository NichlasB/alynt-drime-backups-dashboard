<?php
/**
 * Admin page diagnostics overview helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.58
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides diagnostics overview labels and support-safe helper rendering.
 *
 * @since 0.1.58
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Diagnostics_Overview_Helpers {

	/**
	 * Gets an operator-facing summary label for restore-readiness aggregates.
	 *
	 * @since 0.1.55
	 *
	 * @param array<string,mixed> $restore_readiness Restore-readiness aggregate counts.
	 * @return string
	 */
	private function restore_readiness_summary_label( array $restore_readiness ) {
		$reporting    = $this->diagnostic_int( $restore_readiness, 'reporting_sites' );
		$available    = $this->diagnostic_int( $restore_readiness, 'evidence_sites' );
		$incomplete   = $this->diagnostic_int( $restore_readiness, 'incomplete_sites' );
		$stale        = $this->diagnostic_int( $restore_readiness, 'stale_sites' );
		$incompatible = $this->diagnostic_int( $restore_readiness, 'incompatible_sites' );
		$unknown      = $this->diagnostic_int( $restore_readiness, 'unknown_sites' );

		if ( 0 === $reporting ) {
			return __( 'No restore evidence reported yet', 'alynt-drime-backups-dashboard' );
		}

		if ( $incompatible > 0 ) {
			return __( 'Incompatible restore evidence reported', 'alynt-drime-backups-dashboard' );
		}

		if ( $stale > 0 ) {
			return __( 'Stale restore evidence reported', 'alynt-drime-backups-dashboard' );
		}

		if ( $incomplete > 0 && $available > 0 ) {
			return __( 'Mixed restore evidence across reporting sites', 'alynt-drime-backups-dashboard' );
		}

		if ( $incomplete > 0 ) {
			return __( 'Evidence incomplete across reporting sites', 'alynt-drime-backups-dashboard' );
		}

		if ( $unknown > 0 ) {
			return __( 'Unknown restore evidence states reported', 'alynt-drime-backups-dashboard' );
		}

		if ( $available === $reporting ) {
			return __( 'Evidence available across reporting sites', 'alynt-drime-backups-dashboard' );
		}

		if ( $available > 0 ) {
			return __( 'Evidence available for some reporting sites', 'alynt-drime-backups-dashboard' );
		}

		return __( 'Not enough restore evidence to summarize', 'alynt-drime-backups-dashboard' );
	}

	/**
	 * Formats aggregate restore-readiness candidate counts for one source.
	 *
	 * @since 0.1.55
	 *
	 * @param array<string,mixed> $restore_readiness Restore-readiness aggregate counts.
	 * @param string              $source Source key.
	 * @return string
	 */
	private function restore_readiness_source_count_label( array $restore_readiness, $source ) {
		$source     = sanitize_key( (string) $source );
		$total      = $this->diagnostic_int( $restore_readiness, $source . '_candidates' );
		$complete   = $this->diagnostic_int( $restore_readiness, $source . '_complete' );
		$incomplete = $this->diagnostic_int( $restore_readiness, $source . '_incomplete' );

		if ( 0 === $total ) {
			return __( '0 reported', 'alynt-drime-backups-dashboard' );
		}

		return sprintf(
			/* translators: 1: total candidates. 2: complete candidates. 3: incomplete candidates. */
			__( '%1$d reported · %2$d complete · %3$d incomplete', 'alynt-drime-backups-dashboard' ),
			$total,
			$complete,
			$incomplete
		);
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
		$this->render_detail_row( __( 'Remote-action boundary', 'alynt-drime-backups-dashboard' ), __( 'Only explicitly opted-in signed client actions are available; the dashboard stores no Drime API credentials.', 'alynt-drime-backups-dashboard' ) );
		$this->render_detail_row( __( 'Capability gate', 'alynt-drime-backups-dashboard' ), __( 'Remote-action controls appear only when the latest client report advertises the specific supported action capability.', 'alynt-drime-backups-dashboard' ) );
		$this->render_detail_row( __( 'Diagnostics boundary', 'alynt-drime-backups-dashboard' ), __( 'Refreshing or exporting Diagnostics reads support-safe dashboard data only; it does not poll clients, run remote actions, change schedules, or mutate backups.', 'alynt-drime-backups-dashboard' ) );
		$this->render_detail_row( __( 'Deployment boundary', 'alynt-drime-backups-dashboard' ), __( 'Diagnostics does not deploy, update, roll back, or install dashboard packages; live-site changes stay behind the separate deployment approval gate.', 'alynt-drime-backups-dashboard' ) );
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
