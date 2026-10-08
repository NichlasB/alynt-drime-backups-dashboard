<?php
/**
 * Admin page diagnostics audit history helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.73
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders local operator action history diagnostics.
 *
 * @since 0.1.73
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Diagnostics_Audit_History {
	/**
	 * Renders always-on local operator action history.
	 *
	 * @param array<string,mixed> $audit Audit diagnostics.
	 * @return void
	 */
	private function render_audit_history_diagnostics( array $audit ) {
		$summary = isset( $audit['summary'] ) && is_array( $audit['summary'] ) ? $audit['summary'] : array();
		$events  = isset( $audit['events'] ) && is_array( $audit['events'] ) ? $audit['events'] : array();

		echo '<div class="adbd-panel"><h3>' . esc_html__( 'Operator Action History', 'alynt-drime-backups-dashboard' ) . '</h3>';
		echo '<div class="adbd-panel-body">';
		echo '<p>' . esc_html__( 'This always-on local audit history records dashboard actions such as pairing-token creation, local revocation, manual checks, scheduled polling pause/resume, and diagnostics changes. It is separate from optional diagnostic logging and stores only redacted context.', 'alynt-drime-backups-dashboard' ) . '</p>';
		echo '</div>';
		echo '<table class="widefat striped adbd-detail-table" aria-label="' . esc_attr__( 'Operator action history summary', 'alynt-drime-backups-dashboard' ) . '"><tbody>';
		$this->render_detail_row( __( 'Retained actions', 'alynt-drime-backups-dashboard' ), isset( $summary['total'] ) ? (string) (int) $summary['total'] : '0' );
		$this->render_detail_row( __( 'Last action', 'alynt-drime-backups-dashboard' ), $this->date_or_dash( isset( $summary['last_action_at'] ) ? (string) $summary['last_action_at'] : '' ) );
		$this->render_detail_row( __( 'Retention window', 'alynt-drime-backups-dashboard' ), sprintf( /* translators: %d: retention days. */ __( '%d days', 'alynt-drime-backups-dashboard' ), Alynt_Drime_Backups_Dashboard_Event_Log::AUDIT_RETENTION_DAYS ) );
		$this->render_detail_row( __( 'Storage limit', 'alynt-drime-backups-dashboard' ), sprintf( /* translators: %d: maximum retained audit events. */ __( '%d actions', 'alynt-drime-backups-dashboard' ), Alynt_Drime_Backups_Dashboard_Event_Log::AUDIT_MAX_EVENTS ) );
		echo '</tbody></table>';

		$this->render_audit_history_table( $events );

		echo '</div>';
	}

	/**
	 * Renders recent audit events.
	 *
	 * @param array<int,array<string,mixed>> $events Events.
	 * @return void
	 */
	private function render_audit_history_table( array $events ) {
		echo '<h4>' . esc_html__( 'Recent operator actions', 'alynt-drime-backups-dashboard' ) . '</h4>';

		if ( empty( $events ) ) {
			echo '<div class="adbd-empty-state"><h3>' . esc_html__( 'No operator actions yet', 'alynt-drime-backups-dashboard' ) . '</h3>';
			echo '<p>' . esc_html__( 'Dashboard-local actions will appear here after an administrator creates a pairing token, runs Check Now, pauses or resumes scheduled polling, revokes a local record, or changes diagnostics settings.', 'alynt-drime-backups-dashboard' ) . '</p></div>';
			return;
		}

		echo '<table class="widefat striped" aria-label="' . esc_attr__( 'Recent operator actions', 'alynt-drime-backups-dashboard' ) . '"><thead><tr>';
		echo '<th scope="col">' . esc_html__( 'Time', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Actor', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Action', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Outcome', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Context', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '</tr></thead><tbody>';

		foreach ( $events as $event ) {
			$context = isset( $event['context'] ) && is_array( $event['context'] ) ? wp_json_encode( $event['context'] ) : '{}';

			echo '<tr>';
			echo '<td>' . esc_html( $this->date_or_dash( isset( $event['timestamp'] ) ? (string) $event['timestamp'] : '' ) ) . '</td>';
			echo '<td>' . esc_html( $this->audit_actor_label( isset( $event['actor_id'] ) ? (int) $event['actor_id'] : 0 ) ) . '</td>';
			echo '<td>' . esc_html( $this->audit_action_label( isset( $event['action'] ) ? (string) $event['action'] : '' ) ) . '</td>';
			echo '<td>' . esc_html( isset( $event['outcome'] ) ? (string) $event['outcome'] : '' ) . '</td>';
			echo '<td><code>' . esc_html( false === $context ? '{}' : $context ) . '</code></td>';
			echo '</tr>';
		}

		echo '</tbody></table>';
	}

	/**
	 * Formats an audit actor label without exposing account details.
	 *
	 * @param int $actor_id WordPress user ID.
	 * @return string
	 */
	private function audit_actor_label( $actor_id ) {
		$actor_id = max( 0, (int) $actor_id );

		if ( $actor_id <= 0 ) {
			return __( 'Unknown user', 'alynt-drime-backups-dashboard' );
		}

		return sprintf(
			/* translators: %d: WordPress user ID. */
			__( 'User ID %d', 'alynt-drime-backups-dashboard' ),
			$actor_id
		);
	}

	/**
	 * Formats an audit action for operator-facing diagnostics.
	 *
	 * @param string $action Stored audit action slug.
	 * @return string
	 */
	private function audit_action_label( $action ) {
		$action = sanitize_key( (string) $action );
		$labels = array(
			'check_status_now'          => __( 'Check Now', 'alynt-drime-backups-dashboard' ),
			'clear_diagnostics_events'  => __( 'Clear Diagnostics Events', 'alynt-drime-backups-dashboard' ),
			'cleanup_preview'           => __( 'Cleanup Preview', 'alynt-drime-backups-dashboard' ),
			'create_pending_site'       => __( 'Create Pairing Token', 'alynt-drime-backups-dashboard' ),
			'pause_polling'             => __( 'Pause Polling', 'alynt-drime-backups-dashboard' ),
			'preview_schedule_rollback' => __( 'Preview Schedule Rollback', 'alynt-drime-backups-dashboard' ),
			'request_backup_now'        => __( 'Request Backup Now', 'alynt-drime-backups-dashboard' ),
			'resume_polling'            => __( 'Resume Polling', 'alynt-drime-backups-dashboard' ),
			'revoke_local'              => __( 'Revoke Local Pairing', 'alynt-drime-backups-dashboard' ),
			'schedule_apply'            => __( 'Apply Schedule Change', 'alynt-drime-backups-dashboard' ),
			'schedule_preview'          => __( 'Preview Schedule Change', 'alynt-drime-backups-dashboard' ),
			'update_diagnostics'        => __( 'Update Diagnostics Settings', 'alynt-drime-backups-dashboard' ),
		);

		return isset( $labels[ $action ] ) ? $labels[ $action ] : $action;
	}
}
