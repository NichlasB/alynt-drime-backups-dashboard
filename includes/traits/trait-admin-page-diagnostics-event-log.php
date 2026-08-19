<?php
/**
 * Admin page diagnostics event log.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders structured event-log diagnostics and export controls.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Diagnostics_Event_Log {
	/**
	 * Renders structured event-log diagnostics.
	 *
	 * @param array<string,mixed> $logging Logging diagnostics.
	 * @return void
	 */
	private function render_event_log_diagnostics( array $logging ) {
		$settings = isset( $logging['settings'] ) && is_array( $logging['settings'] ) ? $logging['settings'] : array();
		$summary  = isset( $logging['summary'] ) && is_array( $logging['summary'] ) ? $logging['summary'] : array();
		$events   = isset( $logging['events'] ) && is_array( $logging['events'] ) ? $logging['events'] : array();
		$audit    = isset( $logging['audit'] ) && is_array( $logging['audit'] ) ? $logging['audit'] : array();
		$export   = wp_json_encode( $this->event_log->recent_events( 200 ), JSON_PRETTY_PRINT );

		$this->render_audit_history_diagnostics( $audit );

		echo '<div class="adbd-panel"><h3>' . esc_html__( 'Structured Event Log', 'alynt-drime-backups-dashboard' ) . '</h3>';
		echo '<table class="widefat striped adbd-detail-table" aria-label="' . esc_attr__( 'Structured event log summary', 'alynt-drime-backups-dashboard' ) . '"><tbody>';
		$this->render_detail_row( __( 'Logging enabled', 'alynt-drime-backups-dashboard' ), ! empty( $settings['enabled'] ) ? __( 'Yes', 'alynt-drime-backups-dashboard' ) : __( 'No', 'alynt-drime-backups-dashboard' ) );
		$this->render_detail_row( __( 'Minimum severity', 'alynt-drime-backups-dashboard' ), isset( $settings['minimum_level'] ) ? (string) $settings['minimum_level'] : 'warning' );
		$this->render_detail_row( __( 'Event storage backend', 'alynt-drime-backups-dashboard' ), __( 'Autoload-disabled option ring buffer', 'alynt-drime-backups-dashboard' ) );
		$this->render_detail_row( __( 'Retained events', 'alynt-drime-backups-dashboard' ), isset( $summary['total'] ) ? (string) (int) $summary['total'] : '0' );
		$this->render_detail_row( __( 'Last event', 'alynt-drime-backups-dashboard' ), $this->date_or_dash( isset( $summary['last_event_at'] ) ? (string) $summary['last_event_at'] : '' ) );
		echo '</tbody></table>';

		$this->render_event_log_table( $events );

		echo '<div class="adbd-panel-body"><h4>' . esc_html__( 'Redacted Event Export', 'alynt-drime-backups-dashboard' ) . '</h4>';
		echo '<p>' . esc_html__( 'Copy this local, redacted event export only when support needs diagnostic event context. Secret-bearing keys are masked before storage and export.', 'alynt-drime-backups-dashboard' ) . '</p>';
		if ( false === $export ) {
			echo '<div class="notice notice-error inline" role="alert"><p>' . esc_html__( 'The redacted event export could not be prepared. Please try again after refreshing the page.', 'alynt-drime-backups-dashboard' ) . '</p></div>';
		}
		echo '<textarea id="adbd-event-export" class="large-text code" rows="10" readonly="readonly" aria-label="' . esc_attr__( 'Redacted diagnostics event export', 'alynt-drime-backups-dashboard' ) . '">';
		echo esc_textarea( false === $export ? '[]' : $export );
		echo '</textarea>';
		echo '<p class="adbd-actions"><button type="button" class="button adbd-copy-button" hidden data-copy-target="adbd-event-export" data-busy-label="' . esc_attr__( 'Copying…', 'alynt-drime-backups-dashboard' ) . '" data-success-message="' . esc_attr__( 'Redacted event export copied to the clipboard.', 'alynt-drime-backups-dashboard' ) . '" data-error-message="' . esc_attr__( 'The export could not be copied automatically. Select it and copy it manually.', 'alynt-drime-backups-dashboard' ) . '">' . esc_html__( 'Copy Event Export', 'alynt-drime-backups-dashboard' ) . '</button></p>';
		echo '<p class="adbd-copy-status" role="status" aria-live="polite"></p>';

		if ( ! empty( $events ) ) {
			$confirm_clear   = isset( $_GET['confirm_clear'] ) && '1' === sanitize_key( wp_unslash( $_GET['confirm_clear'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only presentation state.
			$diagnostics_url = add_query_arg(
				array(
					'page' => self::MENU_SLUG,
					'tab'  => 'diagnostics',
				),
				admin_url( 'tools.php' )
			);

			if ( $confirm_clear ) {
				?>
				<div class="notice notice-warning inline"><p><strong><?php esc_html_e( 'Confirm clearing the local diagnostics event buffer.', 'alynt-drime-backups-dashboard' ); ?></strong> <?php esc_html_e( 'This permanently removes the retained redacted events shown above. It does not change site snapshots, pairing, polling, client sites, backups, or Drime data.', 'alynt-drime-backups-dashboard' ); ?></p></div>
				<form method="post" class="adbd-actions">
					<?php wp_nonce_field( 'alynt_drime_backups_dashboard_clear_diagnostics_events' ); ?>
					<input type="hidden" name="alynt_drime_backups_dashboard_action" value="clear_diagnostics_events">
					<button type="submit" class="button adbd-button-danger" data-busy-label="<?php esc_attr_e( 'Clearing…', 'alynt-drime-backups-dashboard' ); ?>"><?php esc_html_e( 'Confirm Clear Diagnostics Events', 'alynt-drime-backups-dashboard' ); ?></button>
					<a class="button" href="<?php echo esc_url( $diagnostics_url ); ?>"><?php esc_html_e( 'Cancel', 'alynt-drime-backups-dashboard' ); ?></a>
				</form>
				<?php
			} else {
				$confirm_url = add_query_arg( 'confirm_clear', '1', $diagnostics_url );
				echo '<p><a class="button adbd-button-danger" href="' . esc_url( $confirm_url ) . '">' . esc_html__( 'Review Clearing Diagnostics Events', 'alynt-drime-backups-dashboard' ) . '</a></p>';
			}
		}

		echo '</div></div>';
	}

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
		echo '<p>' . esc_html__( 'This always-on local audit history records dashboard actions such as pairing-token creation, local revocation, manual checks, and diagnostics changes. It is separate from optional diagnostic logging and stores only redacted context.', 'alynt-drime-backups-dashboard' ) . '</p>';
		echo '</div>';
		echo '<table class="widefat striped adbd-detail-table" aria-label="' . esc_attr__( 'Operator action history summary', 'alynt-drime-backups-dashboard' ) . '"><tbody>';
		$this->render_detail_row( __( 'Retained actions', 'alynt-drime-backups-dashboard' ), isset( $summary['total'] ) ? (string) (int) $summary['total'] : '0' );
		$this->render_detail_row( __( 'Last action', 'alynt-drime-backups-dashboard' ), $this->date_or_dash( isset( $summary['last_action_at'] ) ? (string) $summary['last_action_at'] : '' ) );
		$this->render_detail_row( __( 'Retention window', 'alynt-drime-backups-dashboard' ), sprintf( /* translators: %d: number of days. */ __( '%d days', 'alynt-drime-backups-dashboard' ), Alynt_Drime_Backups_Dashboard_Event_Log::AUDIT_RETENTION_DAYS ) );
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
			echo '<p>' . esc_html__( 'Dashboard-local actions will appear here after an administrator creates a pairing token, runs Check Now, revokes a local record, or changes diagnostics settings.', 'alynt-drime-backups-dashboard' ) . '</p></div>';
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
			echo '<td>' . esc_html( isset( $event['action'] ) ? (string) $event['action'] : '' ) . '</td>';
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
	 * Renders recent structured events.
	 *
	 * @param array<int,array<string,mixed>> $events Events.
	 * @return void
	 */
	private function render_event_log_table( array $events ) {
		echo '<h4>' . esc_html__( 'Recent diagnostic events', 'alynt-drime-backups-dashboard' ) . '</h4>';

		if ( empty( $events ) ) {
			echo '<p>' . esc_html__( 'No structured diagnostics events are currently stored.', 'alynt-drime-backups-dashboard' ) . '</p>';
			return;
		}

		echo '<table class="widefat striped" aria-label="' . esc_attr__( 'Recent diagnostic events', 'alynt-drime-backups-dashboard' ) . '"><thead><tr>';
		echo '<th scope="col">' . esc_html__( 'Time', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Level', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Category', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Code', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Message', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Context', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '</tr></thead><tbody>';

		foreach ( $events as $event ) {
			$context = isset( $event['context'] ) && is_array( $event['context'] ) ? wp_json_encode( $event['context'] ) : '{}';

			echo '<tr>';
			echo '<td>' . esc_html( $this->date_or_dash( isset( $event['timestamp'] ) ? (string) $event['timestamp'] : '' ) ) . '</td>';
			echo '<td>' . esc_html( isset( $event['level'] ) ? (string) $event['level'] : '' ) . '</td>';
			echo '<td>' . esc_html( isset( $event['category'] ) ? (string) $event['category'] : '' ) . '</td>';
			echo '<td>' . esc_html( isset( $event['code'] ) ? (string) $event['code'] : '' ) . '</td>';
			echo '<td>' . esc_html( isset( $event['message'] ) ? (string) $event['message'] : '' ) . '</td>';
			echo '<td><code>' . esc_html( false === $context ? '{}' : $context ) . '</code></td>';
			echo '</tr>';
		}

		echo '</tbody></table>';
	}
}
