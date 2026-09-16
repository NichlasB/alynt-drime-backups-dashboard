<?php
/**
 * Admin page detail helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides base detail-list and snapshot rendering helpers.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Basic_Detail_Helpers {
	/**
	 * Renders a detail table row.
	 *
	 * @param string $label Row label.
	 * @param string $value Row value.
	 * @return void
	 */
	private function render_detail_row( $label, $value ) {
		printf(
			'<tr><th scope="row">%1$s</th><td>%2$s</td></tr>',
			esc_html( $label ),
			esc_html( $value )
		);
	}

	/**
	 * Renders an item in a semantic detail list.
	 *
	 * @param string $label Label.
	 * @param string $value Value or safe markup.
	 * @param bool   $value_is_html Whether the value is already escaped markup.
	 * @return void
	 */
	private function render_detail_item( $label, $value, $value_is_html = false ) {
		echo '<div><dt>' . esc_html( $label ) . '</dt><dd>';
		echo $value_is_html ? $value : esc_html( $value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Markup callers pass output from escaping helpers only.
		echo '</dd></div>';
	}

	/**
	 * Renders a latest snapshot summary without raw payload output.
	 *
	 * @param array<string,mixed>|null $snapshot Snapshot row.
	 * @param array<string,mixed>      $site Site row.
	 * @return void
	 */
	private function render_latest_snapshot_summary( $snapshot, array $site = array() ) {
		echo '<div class="adbd-panel"><h3>' . esc_html__( 'Latest Redacted Snapshot', 'alynt-drime-backups-dashboard' ) . '</h3>';

		if ( empty( $snapshot ) || ! is_array( $snapshot ) ) {
			echo '<div class="adbd-panel-body"><p>' . esc_html__( 'No redacted status snapshot has been stored for this site yet. This is expected while client opt-in or the first valid report is pending.', 'alynt-drime-backups-dashboard' ) . '</p></div></div>';
			return;
		}

		$payload = $this->decoded_snapshot_payload( $snapshot );

		echo '<dl class="adbd-detail-list adbd-evidence-list">';
		$this->render_detail_item( __( 'Observed', 'alynt-drime-backups-dashboard' ), $this->time_html( isset( $snapshot['observed_at'] ) ? $snapshot['observed_at'] : '' ), true );
		$this->render_detail_item( __( 'Schema version', 'alynt-drime-backups-dashboard' ), isset( $snapshot['schema_version'] ) ? (string) (int) $snapshot['schema_version'] : '-' );
		$this->render_detail_item( __( 'Queue', 'alynt-drime-backups-dashboard' ), $this->payload_count( $payload, 'queue_count' ) );
		$this->render_detail_item( __( 'Uploaded', 'alynt-drime-backups-dashboard' ), $this->payload_count( $payload, 'uploaded_count' ) );
		$this->render_detail_item( __( 'Failed', 'alynt-drime-backups-dashboard' ), $this->payload_count( $payload, 'failed_count' ) );
		$this->render_detail_item( __( 'Warnings', 'alynt-drime-backups-dashboard' ), $this->payload_count( $payload, 'warning_count' ) );
		$this->render_detail_item( __( 'Active upload', 'alynt-drime-backups-dashboard' ), ! empty( $payload['active_upload'] ) ? __( 'Yes', 'alynt-drime-backups-dashboard' ) : __( 'No', 'alynt-drime-backups-dashboard' ) );
		$this->render_detail_item( __( 'Cron status', 'alynt-drime-backups-dashboard' ), isset( $payload['cron_status'] ) && '' !== $payload['cron_status'] ? (string) $payload['cron_status'] : '-' );
		echo '</dl>';
		$this->render_backup_sources_detail( $payload, $site );
		echo '</div>';
	}

	/**
	 * Renders dashboard-owned source monitoring policy controls.
	 *
	 * @param array<string,mixed>      $site Site row.
	 * @param array<string,mixed>|null $snapshot Snapshot row.
	 * @return void
	 */
	private function render_source_policy_panel( array $site, $snapshot ) {
		$site_id              = isset( $site['id'] ) ? absint( $site['id'] ) : 0;
		$payload              = is_array( $snapshot ) ? $this->decoded_snapshot_payload( $snapshot ) : array();
		$sources              = $this->backup_sources_from_payload( $payload );
		$has_wpvivid_source   = isset( $sources['wpvivid'] );
		$is_external_optional = $this->source_policy->source_is_external_optional( $site, 'wpvivid' );

		if ( ! $has_wpvivid_source && ! $is_external_optional ) {
			return;
		}

		$next_mode   = $is_external_optional ? Alynt_Drime_Backups_Dashboard_Source_Policy::MODE_REQUIRED : Alynt_Drime_Backups_Dashboard_Source_Policy::MODE_EXTERNAL_OPTIONAL;
		$button_text = $is_external_optional ? __( 'Require Alynt-uploaded WPvivid evidence', 'alynt-drime-backups-dashboard' ) : __( 'Treat WPvivid as external / optional', 'alynt-drime-backups-dashboard' );
		$mode_label  = $is_external_optional ? __( 'External / optional', 'alynt-drime-backups-dashboard' ) : __( 'Alynt-uploaded evidence required', 'alynt-drime-backups-dashboard' );

		echo '<div class="adbd-panel"><h3>' . esc_html__( 'Backup Source Monitoring Policy', 'alynt-drime-backups-dashboard' ) . '</h3><div class="adbd-panel-body">';
		echo '<p>' . esc_html__( 'This dashboard-local policy controls whether WPvivid must have Alynt-uploaded evidence before the site is marked healthy. It does not change WPvivid, the client uploader, backups, or Drime.', 'alynt-drime-backups-dashboard' ) . '</p>';
		echo '<dl class="adbd-detail-list">';
		$this->render_detail_item( __( 'WPvivid monitoring mode', 'alynt-drime-backups-dashboard' ), $mode_label );
		echo '</dl>';
		?>
		<form method="post" class="adbd-actions">
			<?php wp_nonce_field( 'alynt_drime_backups_dashboard_update_source_policy' ); ?>
			<input type="hidden" name="alynt_drime_backups_dashboard_action" value="update_source_policy">
			<input type="hidden" name="dashboard_site_id" value="<?php echo esc_attr( (string) $site_id ); ?>">
			<input type="hidden" name="source_key" value="wpvivid">
			<input type="hidden" name="source_mode" value="<?php echo esc_attr( $next_mode ); ?>">
			<button type="submit" class="button" data-busy-label="<?php esc_attr_e( 'Saving…', 'alynt-drime-backups-dashboard' ); ?>"><?php echo esc_html( $button_text ); ?></button>
			<span class="description"><?php esc_html_e( 'Use this only when WPvivid backups are intentionally handled outside Alynt-uploaded evidence for this site.', 'alynt-drime-backups-dashboard' ); ?></span>
		</form>
		<?php
		echo '</div></div>';
	}

	/**
	 * Decodes a snapshot payload for safe summary fields.
	 *
	 * @param array<string,mixed> $snapshot Snapshot row.
	 * @return array<string,mixed>
	 */
	private function decoded_snapshot_payload( array $snapshot ) {
		if ( isset( $snapshot['decoded_payload'] ) && is_array( $snapshot['decoded_payload'] ) ) {
			return $snapshot['decoded_payload'];
		}

		if ( empty( $snapshot['payload_json'] ) ) {
			return array();
		}

		$decoded = json_decode( (string) $snapshot['payload_json'], true );

		return is_array( $decoded ) ? $decoded : array();
	}

	/**
	 * Gets a non-negative payload count.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @param string              $key Field key.
	 * @return string
	 */
	private function payload_count( array $payload, $key ) {
		return isset( $payload[ $key ] ) ? (string) max( 0, (int) $payload[ $key ] ) : '0';
	}

































}
