<?php
/**
 * Admin page remote action history labels.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides safe operator labels for remote action history rendering.
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Remote_Action_History_Labels {

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
}
