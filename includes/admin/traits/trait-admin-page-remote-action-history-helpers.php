<?php
/**
 * Admin page helper split.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides remote action history rendering and detail helpers.
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Remote_Action_History_Helpers {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Remote_Action_History_Filters;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Remote_Action_History_Cleanup_Details;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Remote_Action_History_Schedule_Details;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Remote_Action_History_Labels;


	/**
	 * Renders recent remote action history without raw payloads.
	 *
	 * @param array<int,array<string,mixed>> $history Remote action history.
	 * @param int                            $site_id Site ID.
	 * @return void
	 */
	private function render_remote_action_history( array $history, $site_id = 0 ) {
		echo '<h4>' . esc_html__( 'Remote Action History', 'alynt-drime-backups-dashboard' ) . '</h4>';

		if ( empty( $history ) ) {
			echo '<p class="description">' . esc_html__( 'No V2 remote action requests are stored for this site yet.', 'alynt-drime-backups-dashboard' ) . '</p>';
			return;
		}

		$filters          = $this->current_remote_action_history_filters();
		$filtered_history = $this->filtered_remote_action_history( $history, $filters );

		$this->render_remote_action_history_filters( $history, $filtered_history, $filters, (int) $site_id );

		if ( empty( $filtered_history ) ) {
			echo '<p class="description">' . esc_html__( 'No remote action requests match the current filters.', 'alynt-drime-backups-dashboard' ) . '</p>';
			return;
		}

		echo '<div class="adbd-table-wrap"><table class="widefat striped adbd-history-table"><caption>' . esc_html__( 'Recent V2 remote action requests for this site', 'alynt-drime-backups-dashboard' ) . '</caption><thead><tr>';
		echo '<th scope="col">' . esc_html__( 'Requested', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Action', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Dashboard', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Client report', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Result', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Details', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '</tr></thead><tbody>';

		foreach ( $filtered_history as $row ) {
			echo '<tr><td>' . $this->time_html( isset( $row['requested_at'] ) ? $row['requested_at'] : '' ) . '</td><td>' . esc_html( $this->remote_action_label( isset( $row['action_type'] ) ? (string) $row['action_type'] : '' ) ) . '</td><td>' . esc_html( $this->remote_action_state_label( isset( $row['state'] ) ? (string) $row['state'] : '' ) ) . '</td><td>' . esc_html( $this->remote_action_client_report_label( $row ) ) . '</td><td>' . esc_html( $this->remote_action_result_label( $row ) ) . '</td>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- time_html() returns escaped markup; other dynamic fields are escaped inline.
			$this->render_remote_action_details_cell( $row );
			echo '</tr>';
		}

		echo '</tbody></table></div>';
	}

	/**
	 * Renders a compact detail cell while keeping full support-safe details available.
	 *
	 * @param array<string,mixed> $row History row.
	 * @return void
	 */
	private function render_remote_action_details_cell( array $row ) {
		$full_detail    = $this->remote_action_details_label( $row );
		$summary_detail = $this->remote_action_details_summary_label( $row, $full_detail );

		echo '<td>';

		if ( '-' === $full_detail || $summary_detail === $full_detail ) {
			echo esc_html( $summary_detail );
			echo '</td>';
			return;
		}

		echo '<span class="adbd-history-detail-summary">' . esc_html( $summary_detail ) . '</span>';
		echo '<details class="adbd-history-detail-disclosure"><summary>' . esc_html__( 'Details', 'alynt-drime-backups-dashboard' ) . '</summary><span>' . esc_html( $full_detail ) . '</span></details>';
		echo '</td>';
	}

	/**
	 * Gets the short default detail text for a history row.
	 *
	 * @param array<string,mixed> $row History row.
	 * @param string              $full_detail Full detail label.
	 * @return string
	 */
	private function remote_action_details_summary_label( array $row, $full_detail ) {
		$cleanup_summary = $this->remote_action_cleanup_summary_label( $row );

		if ( '' !== $cleanup_summary ) {
			return $cleanup_summary;
		}

		$schedule_summary = $this->remote_action_schedule_summary_label( $row );

		if ( '' !== $schedule_summary ) {
			return $schedule_summary;
		}

		return (string) $full_detail;
	}

	/**
	 * Gets a compact action-count summary for a history row.
	 *
	 * @param array<string,mixed> $row History row.
	 * @return string
	 */
	private function remote_action_details_label( array $row ) {
		$cleanup_details = $this->remote_action_cleanup_details_label( $row );

		if ( '' !== $cleanup_details ) {
			return $cleanup_details;
		}

		$schedule_details = $this->remote_action_schedule_details_label( $row );

		if ( '' !== $schedule_details ) {
			return $schedule_details;
		}

		if ( empty( $row['client_counts_json'] ) ) {
			return '-';
		}

		$counts = json_decode( (string) $row['client_counts_json'], true );

		if ( ! is_array( $counts ) ) {
			return '-';
		}

		return sprintf(
			/* translators: 1: found count, 2: queued count, 3: already-known count, 4: attempted count, 5: failed count. */
			__( 'Found %1$d; Queued %2$d; Known %3$d; Attempts %4$d; Failed %5$d', 'alynt-drime-backups-dashboard' ),
			isset( $counts['found'] ) ? max( 0, (int) $counts['found'] ) : 0,
			isset( $counts['queued'] ) ? max( 0, (int) $counts['queued'] ) : 0,
			isset( $counts['already_known'] ) ? max( 0, (int) $counts['already_known'] ) : 0,
			isset( $counts['upload_attempted'] ) ? max( 0, (int) $counts['upload_attempted'] ) : 0,
			isset( $counts['failed'] ) ? max( 0, (int) $counts['failed'] ) : 0
		);
	}
}
