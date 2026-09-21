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
 * Provides remote action history filter rendering helpers.
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Remote_Action_History_Filters {

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
			Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCHEDULE_ROLLBACK_PREVIEW => __( 'Schedule Rollback Preview', 'alynt-drime-backups-dashboard' ),
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
}
