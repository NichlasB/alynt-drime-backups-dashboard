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
trait 
Alynt_Drime_Backups_Dashboard_Admin_Page_Remote_Action_History_Helpers
 {

	/**
	 * Renders recent remote action history without raw payloads.
	 *
	 * @param array<int,array<string,mixed>> $history Remote action history.
	 * @return void
	 */
	private function render_remote_action_history( array $history ) {
		echo '<h4>' . esc_html__( 'Remote Action History', 'alynt-drime-backups-dashboard' ) . '</h4>';

		if ( empty( $history ) ) {
			echo '<p class="description">' . esc_html__( 'No V2 remote action requests are stored for this site yet.', 'alynt-drime-backups-dashboard' ) . '</p>';
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

		foreach ( $history as $row ) {
			echo '<tr><td>' . $this->time_html( isset( $row['requested_at'] ) ? $row['requested_at'] : '' ) . '</td><td>' . esc_html( $this->remote_action_label( isset( $row['action_type'] ) ? (string) $row['action_type'] : '' ) ) . '</td><td>' . esc_html( $this->remote_action_state_label( isset( $row['state'] ) ? (string) $row['state'] : '' ) ) . '</td><td>' . esc_html( $this->remote_action_client_report_label( $row ) ) . '</td><td>' . esc_html( $this->remote_action_result_label( $row ) ) . '</td><td>' . esc_html( $this->remote_action_details_label( $row ) ) . '</td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- time_html() returns escaped markup.
		}

		echo '</tbody></table></div>';
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
	 * Gets a compact schedule-management summary for a history row.
	 *
	 * @param array<string,mixed> $row History row.
	 * @return string
	 */
	private function remote_action_schedule_details_label( array $row ) {
		$context = ! empty( $row['redacted_context_json'] ) ? json_decode( (string) $row['redacted_context_json'], true ) : array();

		if ( ! is_array( $context ) ) {
			return '';
		}

		$preview = isset( $context['schedule_preview'] ) && is_array( $context['schedule_preview'] ) ? $context['schedule_preview'] : array();
		$apply   = isset( $context['schedule_apply'] ) && is_array( $context['schedule_apply'] ) ? $context['schedule_apply'] : array();

		if ( ! empty( $apply['previous_cadence'] ) || ! empty( $apply['applied_cadence'] ) ) {
			$previous = ! empty( $apply['previous_cadence'] ) ? $this->schedule_cadence_label( (string) $apply['previous_cadence'] ) : __( 'Unknown', 'alynt-drime-backups-dashboard' );
			$applied  = ! empty( $apply['applied_cadence'] ) ? $this->schedule_cadence_label( (string) $apply['applied_cadence'] ) : __( 'Unknown', 'alynt-drime-backups-dashboard' );
			$detail   = sprintf(
				/* translators: 1: previous cadence label, 2: applied cadence label. */
				__( '%1$s → %2$s', 'alynt-drime-backups-dashboard' ),
				$previous,
				$applied
			);

			if ( ! empty( $apply['new_next_run_at'] ) ) {
				$detail .= '; ' . sprintf(
					/* translators: %s: next run date/time. */
					__( 'Next run %s', 'alynt-drime-backups-dashboard' ),
					$this->datetime_label( (string) $apply['new_next_run_at'] )
				);
			}

			if ( ! empty( $apply['rollback_metadata'] ) && is_array( $apply['rollback_metadata'] ) && ! empty( $apply['rollback_metadata']['captured'] ) ) {
				$detail .= '; ' . __( 'Rollback metadata captured; rollback unavailable', 'alynt-drime-backups-dashboard' );
			}

			return $detail;
		}

		if ( ! empty( $preview['current_cadence'] ) || ! empty( $preview['proposed_cadence'] ) ) {
			$current  = ! empty( $preview['current_cadence'] ) ? $this->schedule_cadence_label( (string) $preview['current_cadence'] ) : __( 'Unknown', 'alynt-drime-backups-dashboard' );
			$proposed = ! empty( $preview['proposed_cadence'] ) ? $this->schedule_cadence_label( (string) $preview['proposed_cadence'] ) : __( 'Unknown', 'alynt-drime-backups-dashboard' );

			return sprintf(
				/* translators: 1: current cadence label, 2: proposed cadence label. */
				__( 'Preview: %1$s → %2$s', 'alynt-drime-backups-dashboard' ),
				$current,
				$proposed
			);
		}

		return '';
	}
}
