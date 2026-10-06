<?php
/**
 * Admin page status history detail helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides recent status history and fixture guide rendering helpers for dashboard admin screens.
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Status_History_Detail_Helpers {

	use Alynt_Drime_Backups_Dashboard_Admin_Page_Attention_Recovery_History_Helpers;

	/**
	 * Renders a bounded recent snapshot history table.
	 *
	 * @param array<int,array<string,mixed>> $history Snapshot history.
	 * @return void
	 */
	private function render_recent_history( array $history ) {
		echo '<div class="adbd-panel"><h3>' . esc_html__( 'Recent Status History', 'alynt-drime-backups-dashboard' ) . '</h3>';

		if ( empty( $history ) ) {
			echo '<div class="adbd-panel-body"><p>' . esc_html__( 'No status history is available yet.', 'alynt-drime-backups-dashboard' ) . '</p></div></div>';
			return;
		}

		echo '<div class="adbd-table-wrap"><table class="widefat striped adbd-history-table"><caption>' . esc_html__( 'The ten most recent retained redacted snapshots for this site', 'alynt-drime-backups-dashboard' ) . '</caption><thead><tr><th scope="col">' . esc_html__( 'Observed', 'alynt-drime-backups-dashboard' ) . '</th><th scope="col">' . esc_html__( 'Status', 'alynt-drime-backups-dashboard' ) . '</th><th scope="col">' . esc_html__( 'Evidence', 'alynt-drime-backups-dashboard' ) . '</th></tr></thead><tbody>';

		foreach ( $history as $row ) {
			$category = isset( $row['overall_status'] ) ? sanitize_key( $row['overall_status'] ) : '';
			$status   = array(
				'category' => $category,
				'label'    => $this->classifier->label( $category ),
			);
			$queue    = number_format_i18n( isset( $row['queue_count'] ) ? max( 0, (int) $row['queue_count'] ) : 0 );
			$uploaded = number_format_i18n( isset( $row['uploaded_count'] ) ? max( 0, (int) $row['uploaded_count'] ) : 0 );
			$failed   = number_format_i18n( isset( $row['failed_count'] ) ? max( 0, (int) $row['failed_count'] ) : 0 );
			$warnings = number_format_i18n( isset( $row['warning_count'] ) ? max( 0, (int) $row['warning_count'] ) : 0 );
			$cron     = isset( $row['cron_status'] ) && '' !== $row['cron_status'] ? $row['cron_status'] : '-';

			echo '<tr><td>' . $this->time_html( isset( $row['observed_at'] ) ? $row['observed_at'] : '' ) . '</td><td>' . $this->status_badge( $status ) . '</td><td>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Helper markup is escaped.
			printf(
				/* translators: 1: queue count, 2: uploaded count, 3: failed count, 4: warning count, 5: cron status. */
				esc_html__( 'Queue %1$s; Uploaded %2$s; Failed %3$s; Warnings %4$s; Cron %5$s', 'alynt-drime-backups-dashboard' ),
				esc_html( $queue ),
				esc_html( $uploaded ),
				esc_html( $failed ),
				esc_html( $warnings ),
				esc_html( $cron )
			);
			echo '</td></tr>';
		}

		echo '</tbody></table></div></div>';
	}

	/**
	 * Gets a safe status label for history panels.
	 *
	 * @param string $category Status category.
	 * @return string
	 */
	private function status_history_category_label( $category ) {
		$category = sanitize_key( $category );

		if ( isset( $this->classifier ) && is_object( $this->classifier ) && method_exists( $this->classifier, 'label' ) ) {
			return (string) $this->classifier->label( $category );
		}

		$labels = array(
			'working'         => __( 'Working', 'alynt-drime-backups-dashboard' ),
			'pending'         => __( 'Pending', 'alynt-drime-backups-dashboard' ),
			'paused'          => __( 'Paused', 'alynt-drime-backups-dashboard' ),
			'incompatible'    => __( 'Incompatible', 'alynt-drime-backups-dashboard' ),
			'not_reporting'   => __( 'Not reporting', 'alynt-drime-backups-dashboard' ),
			'needs_attention' => __( 'Needs attention', 'alynt-drime-backups-dashboard' ),
			'not_configured'  => __( 'Not configured', 'alynt-drime-backups-dashboard' ),
		);

		return isset( $labels[ $category ] ) ? $labels[ $category ] : $category;
	}

	/**
	 * Gets compact evidence text for a snapshot-history row.
	 *
	 * @param array<string,mixed> $row Snapshot history row.
	 * @return string
	 */
	private function snapshot_history_evidence_text( array $row ) {
		return sprintf(
			/* translators: 1: queue count, 2: uploaded count, 3: failed count, 4: warning count, 5: cron status. */
			__( 'Queue %1$s; Uploaded %2$s; Failed %3$s; Warnings %4$s; Cron %5$s', 'alynt-drime-backups-dashboard' ),
			number_format_i18n( isset( $row['queue_count'] ) ? max( 0, (int) $row['queue_count'] ) : 0 ),
			number_format_i18n( isset( $row['uploaded_count'] ) ? max( 0, (int) $row['uploaded_count'] ) : 0 ),
			number_format_i18n( isset( $row['failed_count'] ) ? max( 0, (int) $row['failed_count'] ) : 0 ),
			number_format_i18n( isset( $row['warning_count'] ) ? max( 0, (int) $row['warning_count'] ) : 0 ),
			isset( $row['cron_status'] ) && '' !== $row['cron_status'] ? (string) $row['cron_status'] : '-'
		);
	}

	/**
	 * Renders status category fixture guidance.
	 *
	 * @return void
	 */
	private function render_fixture_status_guide() {
		$categories = array(
			'pending',
			'paused',
			'incompatible',
			'not_reporting',
			'needs_attention',
			'not_configured',
			'working',
		);

		echo '<h3>' . esc_html__( 'Status categories', 'alynt-drime-backups-dashboard' ) . '</h3>';
		echo '<ul>';

		foreach ( $categories as $category ) {
			echo '<li>' . esc_html( $this->classifier->label( $category ) ) . '</li>';
		}

		echo '</ul>';
	}
}
