<?php
/**
 * Admin page Site Detail local record visibility panels.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.45
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders local-only record guidance and archive controls for Site Detail.
 *
 * @since 0.1.45
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Site_Detail_Local_Record_Panels {
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
