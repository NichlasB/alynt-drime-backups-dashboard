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
 *
Provides remote action history rendering and label helpers.
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Remote_Action_History_Helpers {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Remote_Action_History_Filters;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Remote_Action_History_Schedule_Details;


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
	 * Gets a safe operator label for a V2 action type.
	 *
	 * @param string $action_type Action type.
	 * @return string
	 */
	private function remote_action_label( $action_type ) {
		if ( Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCAN_UPLOAD_NOW === sanitize_key( $action_type ) ) {
			return __( 'Request Backup Now', 'alynt-drime-backups-dashboard' );
		}

		if ( Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCHEDULE_PREVIEW === sanitize_key( $action_type ) ) {
			return __( 'Schedule Preview', 'alynt-drime-backups-dashboard' );
		}

		if ( Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCHEDULE_APPLY === sanitize_key( $action_type ) ) {
			return __( 'Schedule Apply', 'alynt-drime-backups-dashboard' );
		}

		if ( Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCHEDULE_ROLLBACK_PREVIEW === sanitize_key( $action_type ) ) {
			return __( 'Schedule Rollback Preview', 'alynt-drime-backups-dashboard' );
		}

		if ( Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_CLEANUP_PREVIEW === sanitize_key( $action_type ) ) {
			return __( 'Cleanup Preview', 'alynt-drime-backups-dashboard' );
		}

		return __( 'Unknown action', 'alynt-drime-backups-dashboard' );
	}

	/**
	 * Gets a safe operator label for a V2 action state.
	 *
	 * @param string $state State.
	 * @return string
	 */
	private function remote_action_state_label( $state ) {
		$labels = array(
			'queued_for_dispatch' => __( 'Queued for dispatch', 'alynt-drime-backups-dashboard' ),
			'dispatch_failed'     => __( 'Dispatch failed', 'alynt-drime-backups-dashboard' ),
			'accepted'            => __( 'Accepted', 'alynt-drime-backups-dashboard' ),
			'rejected'            => __( 'Rejected', 'alynt-drime-backups-dashboard' ),
			'unsupported'         => __( 'Unsupported', 'alynt-drime-backups-dashboard' ),
			'rate_limited'        => __( 'Rate limited', 'alynt-drime-backups-dashboard' ),
			'busy'                => __( 'Busy', 'alynt-drime-backups-dashboard' ),
			'running'             => __( 'Running', 'alynt-drime-backups-dashboard' ),
			'succeeded'           => __( 'Succeeded', 'alynt-drime-backups-dashboard' ),
			'failed'              => __( 'Failed', 'alynt-drime-backups-dashboard' ),
			'timed_out'           => __( 'Timed out', 'alynt-drime-backups-dashboard' ),
			'stale'               => __( 'Stale', 'alynt-drime-backups-dashboard' ),
		);

		$state = sanitize_key( $state );

		return isset( $labels[ $state ] ) ? $labels[ $state ] : __( 'Unknown', 'alynt-drime-backups-dashboard' );
	}

	/**
	 * Gets the latest client-reported action summary from a sanitized payload.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @return array<string,mixed>
	 */
	private function remote_action_last_action( array $payload ) {
		$remote_actions = isset( $payload['remote_actions'] ) && is_array( $payload['remote_actions'] ) ? $payload['remote_actions'] : array();

		return isset( $remote_actions['last_action'] ) && is_array( $remote_actions['last_action'] ) ? $remote_actions['last_action'] : array();
	}

	/**
	 * Gets a client-report label for a history row.
	 *
	 * @param array<string,mixed> $row History row.
	 * @return string
	 */
	private function remote_action_client_report_label( array $row ) {
		if ( empty( $row['client_state'] ) ) {
			return __( 'Awaiting client status', 'alynt-drime-backups-dashboard' );
		}

		return $this->remote_action_state_label( (string) $row['client_state'] );
	}

	/**
	 * Gets a safe result summary for a history row.
	 *
	 * @param array<string,mixed> $row History row.
	 * @return string
	 */
	private function remote_action_result_label( array $row ) {
		if ( ! empty( $row['client_result_summary'] ) ) {
			return (string) $row['client_result_summary'];
		}

		if ( ! empty( $row['result_summary'] ) ) {
			return (string) $row['result_summary'];
		}

		return '-';
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

	/**
	 * Gets a cleanup-preview context from a history row.
	 *
	 * @param array<string,mixed> $row History row.
	 * @return array<string,mixed>
	 */
	private function remote_action_cleanup_preview_context( array $row ) {
		$context = ! empty( $row['redacted_context_json'] ) ? json_decode( (string) $row['redacted_context_json'], true ) : array();

		if ( ! is_array( $context ) || empty( $context['cleanup_preview'] ) || ! is_array( $context['cleanup_preview'] ) ) {
			return array();
		}

		return $context['cleanup_preview'];
	}

	/**
	 * Gets a compact cleanup-preview details summary.
	 *
	 * @param array<string,mixed> $row History row.
	 * @return string
	 */
	private function remote_action_cleanup_summary_label( array $row ) {
		$preview = $this->remote_action_cleanup_preview_context( $row );

		if ( empty( $preview ) ) {
			return '';
		}

		return sprintf(
			/* translators: 1: eligible item count, 2: approximate byte label. */
			__( 'Preview: %1$d eligible temporary items; approx %2$s', 'alynt-drime-backups-dashboard' ),
			isset( $preview['total_eligible_count'] ) ? max( 0, (int) $preview['total_eligible_count'] ) : 0,
			$this->remote_action_bytes_label( isset( $preview['total_approx_bytes'] ) ? (int) $preview['total_approx_bytes'] : 0 )
		);
	}

	/**
	 * Gets support-safe cleanup-preview details.
	 *
	 * @param array<string,mixed> $row History row.
	 * @return string
	 */
	private function remote_action_cleanup_details_label( array $row ) {
		$preview = $this->remote_action_cleanup_preview_context( $row );

		if ( empty( $preview ) ) {
			return '';
		}

		$parts      = array( $this->remote_action_cleanup_summary_label( $row ) );
		$categories = isset( $preview['categories'] ) && is_array( $preview['categories'] ) ? $preview['categories'] : array();

		foreach ( $categories as $category ) {
			if ( ! is_array( $category ) ) {
				continue;
			}

			$parts[] = sprintf(
				/* translators: 1: cleanup category label, 2: eligible count, 3: approximate size, 4: age band, 5: reason code. */
				__( '%1$s: %2$d eligible; approx %3$s; age %4$s; reason %5$s', 'alynt-drime-backups-dashboard' ),
				$this->cleanup_category_label( isset( $category['category'] ) ? (string) $category['category'] : '' ),
				isset( $category['eligible_count'] ) ? max( 0, (int) $category['eligible_count'] ) : 0,
				$this->remote_action_bytes_label( isset( $category['approx_bytes'] ) ? (int) $category['approx_bytes'] : 0 ),
				isset( $category['age_band'] ) && '' !== (string) $category['age_band'] ? sanitize_key( (string) $category['age_band'] ) : __( 'unknown', 'alynt-drime-backups-dashboard' ),
				isset( $category['reason_code'] ) && '' !== (string) $category['reason_code'] ? sanitize_key( (string) $category['reason_code'] ) : __( 'not reported', 'alynt-drime-backups-dashboard' )
			);
		}

		if ( ! empty( $preview['expires_at'] ) ) {
			$parts[] = sprintf(
				/* translators: %s: preview expiry date/time. */
				__( 'Preview expires %s', 'alynt-drime-backups-dashboard' ),
				$this->datetime_label( (string) $preview['expires_at'] )
			);
		}

		$parts[] = __( 'No cleanup, delete, retention, restore, credential, or Drime action was requested', 'alynt-drime-backups-dashboard' );

		return implode( '; ', array_filter( $parts ) );
	}

	/**
	 * Gets a cleanup category operator label.
	 *
	 * @param string $category Category.
	 * @return string
	 */
	private function cleanup_category_label( $category ) {
		return Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::CLEANUP_CATEGORY_UPLOADER_TEMP === sanitize_key( $category )
			? __( 'Uploader temporary artifacts', 'alynt-drime-backups-dashboard' )
			: __( 'Unknown category', 'alynt-drime-backups-dashboard' );
	}

	/**
	 * Gets a compact byte label without exposing paths or object identifiers.
	 *
	 * @param int $bytes Bytes.
	 * @return string
	 */
	private function remote_action_bytes_label( $bytes ) {
		$bytes = max( 0, (int) $bytes );

		if ( function_exists( 'size_format' ) ) {
			return size_format( $bytes );
		}

		if ( $bytes >= 1073741824 ) {
			return round( $bytes / 1073741824, 1 ) . ' GB';
		}

		if ( $bytes >= 1048576 ) {
			return round( $bytes / 1048576, 1 ) . ' MB';
		}

		if ( $bytes >= 1024 ) {
			return round( $bytes / 1024, 1 ) . ' KB';
		}

		return $bytes . ' B';
	}
}
