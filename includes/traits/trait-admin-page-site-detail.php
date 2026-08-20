<?php
/**
 * Admin page Site Detail shell.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders one paired site detail screen.
 *
 * @since 0.1.0
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Site_Detail {
	/**
	 * Renders one site detail shell.
	 *
	 * @return void
	 */
	private function render_site_detail_shell() {
		$site_id   = isset( $_GET['site_id'] ) ? absint( wp_unslash( $_GET['site_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$site      = $site_id > 0 ? $this->sites->get( $site_id ) : null;
		$snapshot  = $site ? $this->snapshots->latest_for_site( $site_id ) : null;
		$sites_url = add_query_arg(
			array(
				'page' => self::MENU_SLUG,
				'tab'  => 'sites',
			),
			admin_url( 'tools.php' )
		);

		echo '<section aria-labelledby="adbd-site-detail-heading">';
		echo '<p class="adbd-back-link"><a href="' . esc_url( $sites_url ) . '">&larr; ' . esc_html__( 'Back to Sites', 'alynt-drime-backups-dashboard' ) . '</a></p>';

		if ( ! $site ) {
			echo '<h2 id="adbd-site-detail-heading">' . esc_html__( 'Site Detail', 'alynt-drime-backups-dashboard' ) . '</h2>';
			echo '<div class="notice notice-error inline" role="alert"><p>' . esc_html__( 'No dashboard site record was found. Return to Sites and choose an existing record.', 'alynt-drime-backups-dashboard' ) . '</p></div></section>';
			return;
		}

		$status         = $this->classifier->classify( $site, $snapshot );
		$endpoint       = rtrim( isset( $site['expected_origin'] ) ? (string) $site['expected_origin'] : '', '/' ) . '/wp-json/alynt-drime-backups-uploader/v1/status';
		$history        = $this->snapshots->recent_for_site( $site_id, 10 );
		$confirm_revoke = isset( $_GET['confirm_revoke'] ) && '1' === sanitize_key( wp_unslash( $_GET['confirm_revoke'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only presentation state.
		$detail_url     = add_query_arg(
			array(
				'page'    => self::MENU_SLUG,
				'tab'     => 'site',
				'site_id' => $site_id,
			),
			admin_url( 'tools.php' )
		);

		echo '<div class="adbd-detail-title"><h2 id="adbd-site-detail-heading">' . esc_html( $this->site_name( $site ) ) . '</h2>' . $this->status_badge( $status ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Badge is escaped by status_badge().
		echo '<p class="adbd-site-meta"><span>' . esc_html( isset( $site['expected_origin'] ) ? $site['expected_origin'] : '' ) . '</span><span>' . esc_html( $this->environment_label( isset( $site['environment'] ) ? $site['environment'] : '' ) ) . '</span><span>' . esc_html( isset( $site['plugin_version'] ) && '' !== $site['plugin_version'] ? sprintf( /* translators: %s: uploader plugin version. */ __( 'Uploader %s', 'alynt-drime-backups-dashboard' ), $site['plugin_version'] ) : __( 'Uploader version unknown', 'alynt-drime-backups-dashboard' ) ) . '</span></p>';
		$status_notice_tone = $this->status_notice_tone( $status['category'] );
		$status_notice_role = 'error' === $status_notice_tone ? 'alert' : 'status';
		echo '<div class="notice notice-' . esc_attr( $status_notice_tone ) . ' inline adbd-status-summary" role="' . esc_attr( $status_notice_role ) . '"><p><strong>' . esc_html( $status['message'] ) . '</strong></p><p>' . esc_html( $this->status_guidance( $status['category'] ) ) . '</p></div>';
		echo '<div class="adbd-actions">';
		$this->render_check_status_form( $site, $site_id, true );
		echo '<span class="description">' . esc_html__( 'A manual check reads the same fixed endpoint used by scheduled polling and cannot change a backup.', 'alynt-drime-backups-dashboard' ) . '</span></div>';

		echo '<div class="adbd-panel-grid">';
		echo '<div class="adbd-panel"><h3>' . esc_html__( 'Enrollment and Identity', 'alynt-drime-backups-dashboard' ) . '</h3><dl class="adbd-detail-list">';
		$this->render_detail_item( __( 'Enrollment state', 'alynt-drime-backups-dashboard' ), $this->enrollment_label( isset( $site['enrollment_status'] ) ? $site['enrollment_status'] : '' ) );
		$this->render_detail_item( __( 'Expected origin', 'alynt-drime-backups-dashboard' ), isset( $site['expected_origin'] ) ? $site['expected_origin'] : '' );
		$this->render_detail_item( __( 'Fixed status endpoint', 'alynt-drime-backups-dashboard' ), '<code>' . esc_html( $endpoint ) . '</code>', true );
		$this->render_detail_item( __( 'Status schema', 'alynt-drime-backups-dashboard' ), isset( $site['payload_schema_version'] ) && '' !== $site['payload_schema_version'] ? (string) (int) $site['payload_schema_version'] : '-' );
		echo '</dl></div>';
		echo '<div class="adbd-panel"><h3>' . esc_html__( 'Polling and Credential State', 'alynt-drime-backups-dashboard' ) . '</h3><dl class="adbd-detail-list">';
		$this->render_detail_item( __( 'Credential state', 'alynt-drime-backups-dashboard' ), $this->credential_state( $site ) );
		$this->render_detail_item( __( 'Last report received', 'alynt-drime-backups-dashboard' ), $this->time_html( isset( $site['last_seen_at'] ) ? $site['last_seen_at'] : '' ), true );
		$this->render_detail_item( __( 'Last poll attempt', 'alynt-drime-backups-dashboard' ), $this->time_html( isset( $site['last_poll_attempt_at'] ) ? $site['last_poll_attempt_at'] : '' ), true );
		$this->render_detail_item( __( 'Next scheduled poll', 'alynt-drime-backups-dashboard' ), $this->time_html( isset( $site['next_poll_at'] ) ? $site['next_poll_at'] : '' ), true );
		$this->render_detail_item( __( 'Consecutive failures', 'alynt-drime-backups-dashboard' ), isset( $site['consecutive_failures'] ) ? (string) max( 0, (int) $site['consecutive_failures'] ) : '0' );
		$this->render_detail_item( __( 'Last safe error', 'alynt-drime-backups-dashboard' ), $this->safe_error_label( $site ) );
		echo '</dl></div></div>';

		$this->render_request_backup_now_panel( $site, $snapshot, $this->remote_actions->recent_for_site( $site_id, 10 ) );
		$this->render_latest_snapshot_summary( $snapshot );
		$this->render_recent_history( $history );

		echo '<div class="adbd-panel adbd-privacy-panel"><h3>' . esc_html__( 'Credential and Privacy Boundary', 'alynt-drime-backups-dashboard' ) . '</h3><div class="adbd-panel-body"><p>' . esc_html__( 'Before enrollment, only a verifier for the display-once pairing token is stored. After enrollment, encrypted per-site polling credential material is stored, but its plaintext is never displayed.', 'alynt-drime-backups-dashboard' ) . '</p><p>' . esc_html__( 'This screen never shows pairing tokens, polling secrets, authorization headers, raw response bodies, filesystem paths, SQL, cookies, nonces, salts, or Drime credentials.', 'alynt-drime-backups-dashboard' ) . '</p></div></div>';

		if ( ! isset( $site['enrollment_status'] ) || 'revoked' !== $site['enrollment_status'] ) {
			echo '<div class="adbd-panel adbd-danger-panel"><h3>' . esc_html__( 'Revoke Local Dashboard Record', 'alynt-drime-backups-dashboard' ) . '</h3><div class="adbd-panel-body"><p>' . esc_html__( 'Revocation marks this local record revoked, clears its pairing and polling credential fields and next-poll state, and stops future polling. Existing snapshots remain under the normal 30-day retention cleanup. It does not contact the client site or Drime.', 'alynt-drime-backups-dashboard' ) . '</p>';

			if ( $confirm_revoke ) {
				echo '<div class="notice notice-warning inline"><p><strong>' . esc_html__( 'Confirm local revocation.', 'alynt-drime-backups-dashboard' ) . '</strong> ' . esc_html__( 'This local credential state cannot be recovered; pairing again requires a new token and client opt-in.', 'alynt-drime-backups-dashboard' ) . '</p></div>';
				?>
				<form method="post" class="adbd-actions">
					<?php wp_nonce_field( 'alynt_drime_backups_dashboard_revoke_local' ); ?>
					<input type="hidden" name="alynt_drime_backups_dashboard_action" value="revoke_local">
					<input type="hidden" name="dashboard_site_id" value="<?php echo esc_attr( (string) $site_id ); ?>">
					<button type="submit" class="button adbd-button-danger" data-busy-label="<?php esc_attr_e( 'Revoking…', 'alynt-drime-backups-dashboard' ); ?>"><?php esc_html_e( 'Confirm Local Revocation', 'alynt-drime-backups-dashboard' ); ?></button>
					<a class="button" href="<?php echo esc_url( $detail_url ); ?>"><?php esc_html_e( 'Cancel', 'alynt-drime-backups-dashboard' ); ?></a>
				</form>
				<?php
			} else {
				$confirm_url = add_query_arg( 'confirm_revoke', '1', $detail_url );
				echo '<p><a class="button adbd-button-danger" href="' . esc_url( $confirm_url ) . '">' . esc_html__( 'Review Local Revocation', 'alynt-drime-backups-dashboard' ) . '</a></p>';
			}

			echo '</div></div>';
		}

		echo '</section>';
	}
}
