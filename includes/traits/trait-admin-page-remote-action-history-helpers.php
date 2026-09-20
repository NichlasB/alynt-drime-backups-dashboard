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
			echo '<tr><td>' . $this->time_html( isset( $row['requested_at'] ) ? $row['requested_at'] : '' ) . '</td><td>' . esc_html( $this->remote_action_label( isset( $row['action_type'] ) ? (string) $row['action_type'] : '' ) ) . '</td><td>' . esc_html( $this->remote_action_state_label( isset( $row['state'] ) ? (string) $row['state'] : '' ) ) . '</td><td>' . esc_html( $this->remote_action_client_report_label( $row ) ) . '</td><td>' . esc_html( $this->remote_action_result_label( $row ) ) . '</td><td>' . esc_html( $this->remote_action_details_label( $row ) ) . '</td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- time_html() returns escaped markup.
		}

		echo '</tbody></table></div>';
	}

	/**
	 * Gets allowlisted remote action history filter values from the request.
	 *
	 * @return array{action_type:string,state:string}
	 */
	private function current_remote_action_history_filters() {
		$action_type = '';
		$state       = '';

		if ( isset( $_GET['adbd_history_action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display filter.
			$raw_action_type = wp_unslash( $_GET['adbd_history_action'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized and allowlisted below.
			$action_type     = is_scalar( $raw_action_type ) ? sanitize_key( (string) $raw_action_type ) : '';
		}

		if ( isset( $_GET['adbd_history_state'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display filter.
			$raw_state = wp_unslash( $_GET['adbd_history_state'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized and allowlisted below.
			$state     = is_scalar( $raw_state ) ? sanitize_key( (string) $raw_state ) : '';
		}

		$action_options = $this->remote_action_history_action_options();
		$state_options  = $this->remote_action_history_state_options();

		return array(
			'action_type' => isset( $action_options[ $action_type ] ) ? $action_type : '',
			'state'       => isset( $state_options[ $state ] ) ? $state : '',
		);
	}

	/**
	 * Filters recent remote action history rows for display.
	 *
	 * @param array<int,array<string,mixed>>         $history Remote action history.
	 * @param array{action_type:string,state:string} $filters Filters.
	 * @return array<int,array<string,mixed>>
	 */
	private function filtered_remote_action_history( array $history, array $filters ) {
		$filtered = array();

		foreach ( $history as $row ) {
			$action_type = isset( $row['action_type'] ) ? sanitize_key( (string) $row['action_type'] ) : '';
			$state       = isset( $row['state'] ) ? sanitize_key( (string) $row['state'] ) : '';

			if ( '' !== $filters['action_type'] && $filters['action_type'] !== $action_type ) {
				continue;
			}

			if ( '' !== $filters['state'] && $filters['state'] !== $state ) {
				continue;
			}

			$filtered[] = $row;
		}

		return $filtered;
	}

	/**
	 * Renders read-only remote action history filters.
	 *
	 * @param array<int,array<string,mixed>>         $history Full history.
	 * @param array<int,array<string,mixed>>         $filtered_history Filtered history.
	 * @param array{action_type:string,state:string} $filters Filters.
	 * @param int                                    $site_id Site ID.
	 * @return void
	 */
	private function render_remote_action_history_filters( array $history, array $filtered_history, array $filters, $site_id ) {
		$action_options = $this->remote_action_history_action_options();
		$state_options  = $this->remote_action_history_state_options();
		$filter_active  = '' !== $filters['action_type'] || '' !== $filters['state'];
		$reset_url      = add_query_arg(
			array(
				'page'    => 'alynt-drime-backups-dashboard',
				'tab'     => 'site',
				'site_id' => max( 0, (int) $site_id ),
			),
			admin_url( 'tools.php' )
		);

		echo '<form method="get" class="tablenav adbd-history-filters">';
		echo '<input type="hidden" name="page" value="alynt-drime-backups-dashboard">';
		echo '<input type="hidden" name="tab" value="site">';
		echo '<input type="hidden" name="site_id" value="' . esc_attr( (string) max( 0, (int) $site_id ) ) . '">';
		echo '<div class="alignleft actions">';
		echo '<label for="adbd-history-action" class="screen-reader-text">' . esc_html__( 'Filter remote action history by action', 'alynt-drime-backups-dashboard' ) . '</label>';
		echo '<select id="adbd-history-action" name="adbd_history_action">';
		foreach ( $action_options as $value => $label ) {
			echo '<option value="' . esc_attr( $value ) . '"' . $this->selected_html_attribute( $filters['action_type'], $value ) . '>' . esc_html( $label ) . '</option>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- selected_html_attribute() returns a fixed escaped attribute fragment.
		}
		echo '</select> ';
		echo '<label for="adbd-history-state" class="screen-reader-text">' . esc_html__( 'Filter remote action history by dashboard state', 'alynt-drime-backups-dashboard' ) . '</label>';
		echo '<select id="adbd-history-state" name="adbd_history_state">';
		foreach ( $state_options as $value => $label ) {
			echo '<option value="' . esc_attr( $value ) . '"' . $this->selected_html_attribute( $filters['state'], $value ) . '>' . esc_html( $label ) . '</option>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- selected_html_attribute() returns a fixed escaped attribute fragment.
		}
		echo '</select> ';
		echo '<button type="submit" class="button">' . esc_html__( 'Filter History', 'alynt-drime-backups-dashboard' ) . '</button>';
		if ( $filter_active ) {
			echo ' <a class="button" href="' . esc_url( $reset_url ) . '">' . esc_html__( 'Reset filters', 'alynt-drime-backups-dashboard' ) . '</a>';
		}
		echo '</div>';
		echo '<p class="description">';
		echo esc_html(
			sprintf(
				/* translators: 1: filtered row count, 2: total row count. */
				__( 'Showing %1$d of %2$d remote action requests.', 'alynt-drime-backups-dashboard' ),
				count( $filtered_history ),
				count( $history )
			)
		);
		echo '</p>';
		echo '</form>';
	}

	/**
	 * Gets the action-type filter options.
	 *
	 * @return array<string,string>
	 */
	private function remote_action_history_action_options() {
		return array(
			'' => __( 'All actions', 'alynt-drime-backups-dashboard' ),
			Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCAN_UPLOAD_NOW => __( 'Request Backup Now', 'alynt-drime-backups-dashboard' ),
			Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCHEDULE_PREVIEW => __( 'Schedule Preview', 'alynt-drime-backups-dashboard' ),
			Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCHEDULE_APPLY => __( 'Schedule Apply', 'alynt-drime-backups-dashboard' ),
		);
	}

	/**
	 * Gets the dashboard-state filter options.
	 *
	 * @return array<string,string>
	 */
	private function remote_action_history_state_options() {
		return array(
			''                    => __( 'All dashboard states', 'alynt-drime-backups-dashboard' ),
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
	}

	/**
	 * Gets a safe selected option attribute.
	 *
	 * @param string $actual Actual value.
	 * @param string $expected Expected value.
	 * @return string
	 */
	private function selected_html_attribute( $actual, $expected ) {
		return (string) $actual === (string) $expected ? ' selected="selected"' : '';
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
			$previous = ! empty( $apply['previous_cadence'] ) ? $this->schedule_cadence_label( (string) $apply['previous_cadence'] ) : '';
			$applied  = ! empty( $apply['applied_cadence'] ) ? $this->schedule_cadence_label( (string) $apply['applied_cadence'] ) : '';
			$detail   = $this->schedule_transition_label(
				$previous,
				$applied,
				__( 'Applied cadence', 'alynt-drime-backups-dashboard' ),
				__( 'previous cadence pending client report', 'alynt-drime-backups-dashboard' ),
				__( 'applied cadence pending client report', 'alynt-drime-backups-dashboard' )
			);

			if ( ! empty( $apply['new_next_run_at'] ) ) {
				$detail .= '; ' . sprintf(
					/* translators: %s: next run date/time. */
					__( 'Next run %s', 'alynt-drime-backups-dashboard' ),
					$this->datetime_label( (string) $apply['new_next_run_at'] )
				);
			}

			$detail .= '; ' . __( 'Alynt uploader scan cadence only; upload worker cadence may remain separate', 'alynt-drime-backups-dashboard' );

			$rollback_label = isset( $apply['rollback_metadata'] ) && is_array( $apply['rollback_metadata'] ) ? $this->remote_action_rollback_metadata_label( $apply['rollback_metadata'] ) : '';
			if ( '' !== $rollback_label ) {
				$detail .= '; ' . $rollback_label;
			}

			return $detail;
		}

		if ( ! empty( $preview['current_cadence'] ) || ! empty( $preview['proposed_cadence'] ) ) {
			$current  = ! empty( $preview['current_cadence'] ) ? $this->schedule_cadence_label( (string) $preview['current_cadence'] ) : '';
			$proposed = ! empty( $preview['proposed_cadence'] ) ? $this->schedule_cadence_label( (string) $preview['proposed_cadence'] ) : '';

			return $this->schedule_transition_label(
				$current,
				$proposed,
				__( 'Preview target', 'alynt-drime-backups-dashboard' ),
				__( 'current cadence pending client report', 'alynt-drime-backups-dashboard' ),
				__( 'proposed cadence pending client report', 'alynt-drime-backups-dashboard' )
			);
		}

		return '';
	}

	/**
	 * Gets a compact rollback-readiness label for a schedule apply row.
	 *
	 * @param array<string,mixed> $metadata Rollback metadata.
	 * @return string
	 */
	private function remote_action_rollback_metadata_label( array $metadata ) {
		if ( empty( $metadata['captured'] ) ) {
			return '';
		}

		$parts  = array(
			__( 'Rollback metadata captured as evidence only; rollback action unavailable', 'alynt-drime-backups-dashboard' ),
		);
		$reason = isset( $metadata['reason'] ) ? sanitize_key( (string) $metadata['reason'] ) : '';

		if ( '' !== $reason ) {
			$parts[] = sprintf(
				/* translators: %s: rollback unavailable reason code. */
				__( 'Reason %s', 'alynt-drime-backups-dashboard' ),
				$reason
			);
		}

		if ( ! empty( $metadata['expires_at'] ) ) {
			$parts[] = sprintf(
				/* translators: %s: rollback metadata expiry date/time. */
				__( 'metadata expires %s', 'alynt-drime-backups-dashboard' ),
				$this->datetime_label( (string) $metadata['expires_at'] )
			);
		}

		return implode( '; ', $parts );
	}
}
