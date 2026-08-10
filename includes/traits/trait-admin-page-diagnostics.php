<?php
/**
 * Admin page diagnostics rendering.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders diagnostics sections for the admin page.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Diagnostics {
	/**
	 * Renders redacted diagnostics.
	 *
	 * @return void
	 */
	private function render_diagnostics_shell() {
		$diagnostics = $this->diagnostics->collect();
		$scheduler   = isset( $diagnostics['scheduler'] ) && is_array( $diagnostics['scheduler'] ) ? $diagnostics['scheduler'] : array();
		$counts      = isset( $diagnostics['counts'] ) && is_array( $diagnostics['counts'] ) ? $diagnostics['counts'] : array();
		$recent      = isset( $diagnostics['recent'] ) && is_array( $diagnostics['recent'] ) ? $diagnostics['recent'] : array();
		$logging     = isset( $diagnostics['logging'] ) && is_array( $diagnostics['logging'] ) ? $diagnostics['logging'] : array();
		$support     = isset( $diagnostics['support'] ) && is_array( $diagnostics['support'] ) ? $diagnostics['support'] : array();

		echo '<h2>' . esc_html__( 'Diagnostics', 'alynt-drime-backups-dashboard' ) . '</h2>';
		echo '<p>' . esc_html__( 'Redacted scheduler and polling diagnostics for operators. This screen never displays pairing tokens, polling secrets, authorization headers, raw response bodies, filesystem paths, SQL, cookies, nonces, salts, or Drime credentials.', 'alynt-drime-backups-dashboard' ) . '</p>';

		$this->render_diagnostics_settings( $logging );

		echo '<h3>' . esc_html__( 'Scheduler', 'alynt-drime-backups-dashboard' ) . '</h3>';
		echo '<table class="widefat striped"><tbody>';
		$this->render_detail_row( __( 'Poll hook', 'alynt-drime-backups-dashboard' ), $this->diagnostic_value( $scheduler, 'poll_hook' ) );
		$this->render_detail_row( __( 'Poll schedule state', 'alynt-drime-backups-dashboard' ), $this->diagnostic_value( $scheduler, 'poll_schedule_state' ) );
		$this->render_detail_row( __( 'Next scheduled poll', 'alynt-drime-backups-dashboard' ), $this->date_or_dash( $this->diagnostic_value( $scheduler, 'poll_next_at' ) ) );
		$this->render_detail_row( __( 'Poll interval', 'alynt-drime-backups-dashboard' ), $this->seconds_label( $this->diagnostic_int( $scheduler, 'poll_interval_seconds' ) ) );
		$this->render_detail_row( __( 'Batch size', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $scheduler, 'poll_batch_size' ) );
		$this->render_detail_row( __( 'Stale threshold', 'alynt-drime-backups-dashboard' ), $this->seconds_label( $this->diagnostic_int( $scheduler, 'stale_after_seconds' ) ) );
		$this->render_detail_row( __( 'Global scheduler lock', 'alynt-drime-backups-dashboard' ), $this->lock_label( isset( $scheduler['global_lock_active'] ) ? $scheduler['global_lock_active'] : null ) );
		$this->render_detail_row( __( 'Current UTC time', 'alynt-drime-backups-dashboard' ), $this->diagnostic_value( $scheduler, 'current_utc' ) );
		echo '</tbody></table>';

		echo '<h3>' . esc_html__( 'History retention', 'alynt-drime-backups-dashboard' ) . '</h3>';
		echo '<table class="widefat striped"><tbody>';
		$this->render_detail_row( __( 'Cleanup hook', 'alynt-drime-backups-dashboard' ), $this->diagnostic_value( $scheduler, 'cleanup_hook' ) );
		$this->render_detail_row( __( 'Cleanup schedule state', 'alynt-drime-backups-dashboard' ), $this->diagnostic_value( $scheduler, 'cleanup_state' ) );
		$this->render_detail_row( __( 'Next cleanup', 'alynt-drime-backups-dashboard' ), $this->date_or_dash( $this->diagnostic_value( $scheduler, 'cleanup_next_at' ) ) );
		$this->render_detail_row( __( 'Retention window', 'alynt-drime-backups-dashboard' ), sprintf( /* translators: %d: retention days. */ __( '%d days', 'alynt-drime-backups-dashboard' ), $this->diagnostic_int( $scheduler, 'retention_days' ) ) );
		$this->render_detail_row( __( 'Cleanup batch size', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $scheduler, 'cleanup_batch_size' ) );
		echo '</tbody></table>';

		echo '<h3>' . esc_html__( 'Site polling summary', 'alynt-drime-backups-dashboard' ) . '</h3>';
		echo '<table class="widefat striped"><tbody>';
		$this->render_detail_row( __( 'Total dashboard sites', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $counts, 'total_sites' ) );
		$this->render_detail_row( __( 'Polling-ready sites', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $counts, 'polling_ready' ) );
		$this->render_detail_row( __( 'Due now', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $counts, 'due_now' ) );
		$this->render_detail_row( __( 'Missing polling credentials', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $counts, 'missing_credentials' ) );
		$this->render_detail_row( __( 'Paused locally', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $counts, 'paused' ) );
		$this->render_detail_row( __( 'Sites with recorded failures', 'alynt-drime-backups-dashboard' ), (string) $this->diagnostic_int( $counts, 'with_failures' ) );
		echo '</tbody></table>';

		$this->render_status_count_table( isset( $counts['statuses'] ) && is_array( $counts['statuses'] ) ? $counts['statuses'] : array() );
		$this->render_recent_poll_outcomes( $recent );
		$this->render_event_log_diagnostics( $logging );
		$this->render_support_copy_output( $support );
	}

	/**
	 * Renders diagnostics logging settings.
	 *
	 * @param array<string,mixed> $logging Logging diagnostics.
	 * @return void
	 */
	private function render_diagnostics_settings( array $logging ) {
		$settings       = isset( $logging['settings'] ) && is_array( $logging['settings'] ) ? $logging['settings'] : $this->event_log->settings();
		$minimum_level  = isset( $settings['minimum_level'] ) ? (string) $settings['minimum_level'] : 'warning';
		$retention_days = isset( $settings['retention_days'] ) ? (int) $settings['retention_days'] : 14;
		$max_events     = isset( $settings['max_events'] ) ? (int) $settings['max_events'] : 200;

		echo '<h3>' . esc_html__( 'Structured diagnostics logging', 'alynt-drime-backups-dashboard' ) . '</h3>';
		echo '<p>' . esc_html__( 'Structured diagnostics logging is disabled by default. When enabled, the dashboard stores a bounded, redacted local event buffer for support troubleshooting.', 'alynt-drime-backups-dashboard' ) . '</p>';
		?>
		<form method="post">
			<?php wp_nonce_field( 'alynt_drime_backups_dashboard_update_diagnostics_settings' ); ?>
			<input type="hidden" name="alynt_drime_backups_dashboard_action" value="update_diagnostics_settings">
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Enable diagnostics logging', 'alynt-drime-backups-dashboard' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="alynt_drime_backups_dashboard_diagnostics[enabled]" value="1" <?php checked( ! empty( $settings['enabled'] ) ); ?>>
							<?php esc_html_e( 'Store redacted local diagnostic events.', 'alynt-drime-backups-dashboard' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="alynt-drime-dashboard-minimum-level"><?php esc_html_e( 'Minimum severity', 'alynt-drime-backups-dashboard' ); ?></label></th>
					<td>
						<select id="alynt-drime-dashboard-minimum-level" name="alynt_drime_backups_dashboard_diagnostics[minimum_level]">
							<?php foreach ( $this->event_log->severity_levels() as $level ) : ?>
								<option value="<?php echo esc_attr( $level ); ?>" <?php selected( $minimum_level, $level ); ?>><?php echo esc_html( ucfirst( $level ) ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="alynt-drime-dashboard-retention-days"><?php esc_html_e( 'Retention days', 'alynt-drime-backups-dashboard' ); ?></label></th>
					<td><input id="alynt-drime-dashboard-retention-days" type="number" min="1" max="90" name="alynt_drime_backups_dashboard_diagnostics[retention_days]" value="<?php echo esc_attr( (string) $retention_days ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="alynt-drime-dashboard-max-events"><?php esc_html_e( 'Maximum retained events', 'alynt-drime-backups-dashboard' ); ?></label></th>
					<td><input id="alynt-drime-dashboard-max-events" type="number" min="10" max="1000" name="alynt_drime_backups_dashboard_diagnostics[max_events]" value="<?php echo esc_attr( (string) $max_events ); ?>"></td>
				</tr>
			</table>
			<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Save Diagnostics Settings', 'alynt-drime-backups-dashboard' ); ?></button></p>
		</form>
		<?php
	}

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
		$export   = wp_json_encode( $this->event_log->recent_events( 200 ), JSON_PRETTY_PRINT );

		echo '<h3>' . esc_html__( 'Structured event log', 'alynt-drime-backups-dashboard' ) . '</h3>';
		echo '<table class="widefat striped"><tbody>';
		$this->render_detail_row( __( 'Logging enabled', 'alynt-drime-backups-dashboard' ), ! empty( $settings['enabled'] ) ? __( 'Yes', 'alynt-drime-backups-dashboard' ) : __( 'No', 'alynt-drime-backups-dashboard' ) );
		$this->render_detail_row( __( 'Minimum severity', 'alynt-drime-backups-dashboard' ), isset( $settings['minimum_level'] ) ? (string) $settings['minimum_level'] : 'warning' );
		$this->render_detail_row( __( 'Event storage backend', 'alynt-drime-backups-dashboard' ), __( 'Autoload-disabled option ring buffer', 'alynt-drime-backups-dashboard' ) );
		$this->render_detail_row( __( 'Retained events', 'alynt-drime-backups-dashboard' ), isset( $summary['total'] ) ? (string) (int) $summary['total'] : '0' );
		$this->render_detail_row( __( 'Last event', 'alynt-drime-backups-dashboard' ), $this->date_or_dash( isset( $summary['last_event_at'] ) ? (string) $summary['last_event_at'] : '' ) );
		echo '</tbody></table>';

		$this->render_event_log_table( $events );

		echo '<h4>' . esc_html__( 'Redacted event export', 'alynt-drime-backups-dashboard' ) . '</h4>';
		echo '<p>' . esc_html__( 'Copy this local, redacted event export only when support needs diagnostic event context. Secret-bearing keys are masked before storage and export.', 'alynt-drime-backups-dashboard' ) . '</p>';
		if ( false === $export ) {
			echo '<div class="notice notice-error inline"><p>' . esc_html__( 'The redacted event export could not be prepared. Please try again after refreshing the page.', 'alynt-drime-backups-dashboard' ) . '</p></div>';
		}
		echo '<textarea class="large-text code" rows="10" readonly="readonly" aria-label="' . esc_attr__( 'Redacted diagnostics event export', 'alynt-drime-backups-dashboard' ) . '">';
		echo esc_textarea( false === $export ? '[]' : $export );
		echo '</textarea>';

		if ( ! empty( $events ) ) {
			?>
			<form method="post">
				<?php wp_nonce_field( 'alynt_drime_backups_dashboard_clear_diagnostics_events' ); ?>
				<input type="hidden" name="alynt_drime_backups_dashboard_action" value="clear_diagnostics_events">
				<p><button type="submit" class="button" onclick="return window.confirm('<?php echo esc_js( __( 'Clear all stored diagnostics events?', 'alynt-drime-backups-dashboard' ) ); ?>');"><?php esc_html_e( 'Clear Diagnostics Events', 'alynt-drime-backups-dashboard' ); ?></button></p>
			</form>
			<?php
		}
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

		echo '<table class="widefat striped"><thead><tr>';
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

	/**
	 * Renders status-count diagnostics.
	 *
	 * @param array<string,int> $statuses Status counts.
	 * @return void
	 */
	private function render_status_count_table( array $statuses ) {
		echo '<h3>' . esc_html__( 'Status distribution', 'alynt-drime-backups-dashboard' ) . '</h3>';

		if ( empty( $statuses ) ) {
			echo '<p>' . esc_html__( 'No status classifications are available yet.', 'alynt-drime-backups-dashboard' ) . '</p>';
			return;
		}

		echo '<table class="widefat striped"><thead><tr>';
		echo '<th scope="col">' . esc_html__( 'Status', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Sites', 'alynt-drime-backups-dashboard' ) . '</th>';
		echo '</tr></thead><tbody>';

		foreach ( $statuses as $category => $total ) {
			echo '<tr>';
			echo '<td>' . esc_html( $this->classifier->label( $category ) ) . '</td>';
			echo '<td>' . esc_html( (string) max( 0, (int) $total ) ) . '</td>';
			echo '</tr>';
		}

		echo '</tbody></table>';
	}

	/**
	 * Renders recent poll outcome diagnostics.
	 *
	 * @param array<int,array<string,mixed>> $recent Recent outcomes.
	 * @return void
	 */
	private function render_recent_poll_outcomes( array $recent ) {
		echo '<h3>' . esc_html__( 'Recent poll outcomes', 'alynt-drime-backups-dashboard' ) . '</h3>';

		if ( empty( $recent ) ) {
			echo '<p>' . esc_html__( 'No poll attempts have been recorded yet.', 'alynt-drime-backups-dashboard' ) . '</p>';
			return;
		}

		echo '<table class="widefat striped"><thead><tr>';
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

		echo '</tbody></table>';
	}

	/**
	 * Renders support-copy diagnostics.
	 *
	 * @param array<string,mixed> $support Support summary.
	 * @return void
	 */
	private function render_support_copy_output( array $support ) {
		$encoded = wp_json_encode( $support, JSON_PRETTY_PRINT );

		echo '<h3>' . esc_html__( 'Support copy', 'alynt-drime-backups-dashboard' ) . '</h3>';
		echo '<p>' . esc_html__( 'Copy this redacted summary when support needs scheduler and polling context. It intentionally omits client domains, site labels, pairing tokens, polling secrets, authorization headers, raw payloads, and raw response bodies.', 'alynt-drime-backups-dashboard' ) . '</p>';
		if ( false === $encoded ) {
			echo '<div class="notice notice-error inline"><p>' . esc_html__( 'The support summary could not be prepared. Please try again after refreshing the page.', 'alynt-drime-backups-dashboard' ) . '</p></div>';
		}
		echo '<textarea class="large-text code" rows="12" readonly="readonly" aria-label="' . esc_attr__( 'Redacted support summary', 'alynt-drime-backups-dashboard' ) . '">';
		echo esc_textarea( false === $encoded ? '{}' : $encoded );
		echo '</textarea>';
	}
}
