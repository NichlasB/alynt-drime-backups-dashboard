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
	 * @param array<string,mixed>|WP_Error|null $result Optional action result.
	 * @return void
	 */
	private function render_site_detail_shell( $result = null ) {
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
		$action_history = $this->remote_actions->recent_for_site( $site_id, 10 );
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
		$this->render_action_opt_in_token_panel( $result, $site_id );
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

		$this->render_polling_pause_panel( $site );
		$this->render_request_backup_now_panel( $site, $snapshot, $action_history );
		$this->render_schedule_management_panel( $snapshot, $site, $action_history );
		$this->render_source_policy_panel( $site, $snapshot );
		$this->render_latest_snapshot_summary( $snapshot, $site );
		$this->render_recent_history( $history );
		$this->render_archive_record_panel( $site, $site_id );
		$this->render_revoked_record_guidance( $site );

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

	/**
	 * Renders local-only guidance for revoked dashboard records.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return void
	 */
	private function render_revoked_record_guidance( array $site ) {
		if ( ! isset( $site['enrollment_status'] ) || 'revoked' !== $site['enrollment_status'] ) {
			return;
		}

		echo '<div class="adbd-panel adbd-warning-panel"><h3>' . esc_html__( 'Revoked Local Dashboard Record', 'alynt-drime-backups-dashboard' ) . '</h3><div class="adbd-panel-body">';
		echo '<p>' . esc_html__( 'This record is retained locally for audit/history. It is not scheduled for polling, and dashboard actions that require active pairing credentials are unavailable.', 'alynt-drime-backups-dashboard' ) . '</p>';
		echo '<p>' . esc_html__( 'To monitor this origin again, create a new pairing token and complete client-site opt-in. This dashboard will not reuse revoked polling or action credentials.', 'alynt-drime-backups-dashboard' ) . '</p>';
		echo '<p>' . esc_html__( 'Permanent local removal is not available in this release. Keeping the revoked record does not contact the client site, change backups, alter Drime, or change client settings.', 'alynt-drime-backups-dashboard' ) . '</p>';
		echo '</div></div>';
	}

	/**
	 * Renders local archive/unarchive controls for non-polling records.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @param int                 $site_id Site ID.
	 * @return void
	 */
	private function render_archive_record_panel( array $site, $site_id ) {
		$is_archived = ! empty( $site['archived_at'] );

		echo '<div class="adbd-panel adbd-warning-panel"><h3>' . esc_html__( 'Local Record Visibility', 'alynt-drime-backups-dashboard' ) . '</h3><div class="adbd-panel-body">';

		if ( $is_archived ) {
			echo '<p>' . esc_html__( 'This dashboard record is archived locally. It remains retained for audit/history, is not shown in the default Sites view, and archive state does not restore or remove any credentials.', 'alynt-drime-backups-dashboard' ) . '</p>';
			$this->render_archive_state_form( $site_id, false );
			echo '</div></div>';
			return;
		}

		if ( ! $this->can_archive_local_record( $site ) ) {
			echo '<p>' . esc_html__( 'Archive is available only for revoked records and expired pending records. Active or credentialed records must be revoked first so archive never hides a record that can still poll or perform signed client actions.', 'alynt-drime-backups-dashboard' ) . '</p>';
			echo '</div></div>';
			return;
		}

		echo '<p>' . esc_html__( 'Archive hides this local record from the default Sites view while retaining it for audit/history. It does not delete data, contact the client site, change backups, alter Drime, or reuse credentials.', 'alynt-drime-backups-dashboard' ) . '</p>';
		$this->render_archive_state_form( $site_id, true );
		echo '</div></div>';
	}

	/**
	 * Renders one local archive-state form.
	 *
	 * @param int  $site_id Site ID.
	 * @param bool $archive Whether the form archives or unarchives.
	 * @return void
	 */
	private function render_archive_state_form( $site_id, $archive ) {
		$nonce  = $archive ? 'alynt_drime_backups_dashboard_archive_local' : 'alynt_drime_backups_dashboard_unarchive_local';
		$action = $archive ? 'archive_local' : 'unarchive_local';
		$label  = $archive ? __( 'Archive Local Record', 'alynt-drime-backups-dashboard' ) : __( 'Unarchive Local Record', 'alynt-drime-backups-dashboard' );
		$busy   = $archive ? __( 'Archiving…', 'alynt-drime-backups-dashboard' ) : __( 'Unarchiving…', 'alynt-drime-backups-dashboard' );
		?>
		<form method="post" class="adbd-actions">
			<?php wp_nonce_field( $nonce ); ?>
			<input type="hidden" name="alynt_drime_backups_dashboard_action" value="<?php echo esc_attr( $action ); ?>">
			<input type="hidden" name="dashboard_site_id" value="<?php echo esc_attr( (string) $site_id ); ?>">
			<button type="submit" class="button" data-busy-label="<?php echo esc_attr( $busy ); ?>"><?php echo esc_html( $label ); ?></button>
		</form>
		<?php
	}
}
