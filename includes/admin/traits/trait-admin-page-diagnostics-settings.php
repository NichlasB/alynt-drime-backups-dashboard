<?php
/**
 * Admin page diagnostics settings.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders structured diagnostics logging settings.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Diagnostics_Settings {
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

		echo '<div class="adbd-panel adbd-settings-panel"><h3>' . esc_html__( 'Structured Diagnostics Logging', 'alynt-drime-backups-dashboard' ) . '</h3><div class="adbd-panel-body">';
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
			<p><button type="submit" class="button button-primary" data-busy-label="<?php esc_attr_e( 'Saving…', 'alynt-drime-backups-dashboard' ); ?>"><?php esc_html_e( 'Save Diagnostics Settings', 'alynt-drime-backups-dashboard' ); ?></button></p>
		</form>
		</div></div>
		<?php
	}
}
