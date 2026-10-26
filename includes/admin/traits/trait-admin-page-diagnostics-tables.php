<?php
/**
 * Admin page diagnostics tables.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders diagnostics count and recent polling tables.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Diagnostics_Tables {
	/**
	 * Renders status-count diagnostics.
	 *
	 * @param array<string,int> $statuses Status counts.
	 * @return void
	 */
	private function render_status_count_table( array $statuses ) {
		echo '<div class="adbd-panel"><h3>' . esc_html__( 'Status Distribution', 'alynt-drime-backups-dashboard' ) . '</h3>';

		if ( empty( $statuses ) ) {
			echo '<div class="adbd-panel-body"><p>' . esc_html__( 'No status classifications are available yet.', 'alynt-drime-backups-dashboard' ) . '</p></div></div>';
			return;
		}

		echo '<table class="widefat striped" aria-label="' . esc_attr__( 'Status distribution diagnostics', 'alynt-drime-backups-dashboard' ) . '"><thead><tr>';
		echo '<th scope="col">' . esc_html__( 'Status', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Sites', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '</tr></thead><tbody>';

		foreach ( $statuses as $category => $total ) {
			echo '<tr>';
			echo '<td>' . esc_html( $this->classifier->label( $category ) ) . '</td>';
			echo '<td>' . esc_html( (string) max( 0, (int) $total ) ) . '</td>';
			echo '</tr>';
		}

		echo '</tbody></table></div>';
	}

	/**
	 * Renders recent poll outcome diagnostics.
	 *
	 * @param array<int,array<string,mixed>> $recent Recent outcomes.
	 * @return void
	 */
	private function render_recent_poll_outcomes( array $recent ) {
		echo '<div class="adbd-panel"><h3>' . esc_html__( 'Recent Poll Outcomes', 'alynt-drime-backups-dashboard' ) . '</h3>';

		if ( empty( $recent ) ) {
			echo '<div class="adbd-panel-body"><p>' . esc_html__( 'No poll attempts have been recorded yet.', 'alynt-drime-backups-dashboard' ) . '</p></div></div>';
			return;
		}

		echo '<table class="widefat striped" aria-label="' . esc_attr__( 'Recent poll outcomes', 'alynt-drime-backups-dashboard' ) . '"><thead><tr>';
		echo '<th scope="col">' . esc_html__( 'Site', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Last attempt', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Last seen', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Next poll', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Status', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Failures', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Last safe error', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '</tr></thead><tbody>';

		foreach ( $recent as $row ) {
			$error = trim( $this->diagnostic_value( $row, 'last_error_code' ) . ' ' . $this->diagnostic_value( $row, 'last_error_summary' ) );

			echo '<tr>';
			echo '<td>' . esc_html( $this->diagnostic_value( $row, 'site_label' ) ) . '</td>';
			echo '<td>' . esc_html( $this->date_or_dash( $this->diagnostic_value( $row, 'last_poll_attempt_at' ) ) ) . '</td>';
			echo '<td>' . esc_html( $this->date_or_dash( $this->diagnostic_value( $row, 'last_seen_at' ) ) ) . '</td>';
			echo '<td>' . esc_html( $this->date_or_dash( $this->diagnostic_value( $row, 'next_poll_at' ) ) ) . '</td>';
			echo '<td>' . esc_html( $this->classifier->label( $this->diagnostic_value( $row, 'overall_status' ) ) ) . '</td>';
			echo '<td>' . esc_html( (string) $this->diagnostic_int( $row, 'consecutive_failures' ) ) . '</td>';
			echo '<td>' . esc_html( '' === $error ? '-' : $error ) . '</td>';
			echo '</tr>';
		}

		echo '</tbody></table></div>';
	}
}
