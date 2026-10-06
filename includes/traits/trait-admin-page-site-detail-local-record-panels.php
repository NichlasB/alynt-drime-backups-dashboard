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
	 * Renders a read-only preview of whether an archived record is ready for future local removal.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @param int                 $site_id Site ID.
	 * @return void
	 */
	private function render_retained_record_removal_preview_panel( array $site, $site_id ) {
		if ( empty( $site['archived_at'] ) ) {
			return;
		}

		$preview     = $this->local_record_removal_preview( $site, $site_id );
		$panel_class = $preview['eligible'] ? 'adbd-panel' : 'adbd-panel adbd-warning-panel';

		echo '<div class="' . esc_attr( $panel_class ) . '"><h3>' . esc_html__( 'Local Removal Preview', 'alynt-drime-backups-dashboard' ) . '</h3><div class="adbd-panel-body">';
		echo '<p>' . esc_html__( 'Preview only. Permanent local removal is not available in this release, and this panel does not delete data, contact the client site, change backups, alter Drime, or change client settings.', 'alynt-drime-backups-dashboard' ) . '</p>';
		echo '<dl class="adbd-detail-list">';
		$this->render_detail_item( __( 'Future removal eligibility', 'alynt-drime-backups-dashboard' ), $preview['eligible'] ? __( 'Eligible for future removal', 'alynt-drime-backups-dashboard' ) : __( 'Not eligible yet', 'alynt-drime-backups-dashboard' ) );
		$this->render_detail_item( __( 'Reason', 'alynt-drime-backups-dashboard' ), $preview['reason'] );
		$this->render_detail_item( __( 'Retained snapshots', 'alynt-drime-backups-dashboard' ), number_format_i18n( $preview['snapshot_count'] ) );
		$this->render_detail_item( __( 'Retained action history rows', 'alynt-drime-backups-dashboard' ), number_format_i18n( $preview['action_count'] ) );
		$this->render_detail_item( __( 'Non-terminal action rows', 'alynt-drime-backups-dashboard' ), number_format_i18n( $preview['non_terminal_action_count'] ) );
		echo '</dl>';
		echo '</div></div>';
	}

	/**
	 * Builds a compact row hint for archived retained-record removal readiness.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @param int                 $site_id Site ID.
	 * @return string
	 */
	private function retained_record_removal_row_hint( array $site, $site_id ) {
		if ( empty( $site['archived_at'] ) ) {
			return '';
		}

		$preview = $this->local_record_removal_preview( $site, $site_id );
		$label   = $preview['eligible']
			? __( 'Local removal: ready for future gate', 'alynt-drime-backups-dashboard' )
			: __( 'Local removal: blocked', 'alynt-drime-backups-dashboard' );
		$counts  = sprintf(
			/* translators: 1: retained snapshot count, 2: retained action-history count, 3: non-terminal action count. */
			__( 'Snapshots %1$s · actions %2$s · non-terminal %3$s', 'alynt-drime-backups-dashboard' ),
			number_format_i18n( $preview['snapshot_count'] ),
			number_format_i18n( $preview['action_count'] ),
			number_format_i18n( $preview['non_terminal_action_count'] )
		);

		return '<span class="description adbd-row-meta"><strong>' . esc_html( $label ) . '</strong><br>' . esc_html( $counts ) . '</span>';
	}

	/**
	 * Builds a read-only local removal preview for one site.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @param int                 $site_id Site ID.
	 * @return array{eligible:bool,reason:string,snapshot_count:int,action_count:int,non_terminal_action_count:int}
	 */
	private function local_record_removal_preview( array $site, $site_id ) {
		$snapshot_count            = $this->count_repository_rows_for_site( 'snapshots', 'count_for_site', $site_id );
		$action_count              = $this->count_repository_rows_for_site( 'remote_actions', 'count_for_site', $site_id );
		$non_terminal_action_count = $this->count_repository_rows_for_site( 'remote_actions', 'count_non_terminal_for_site', $site_id );
		$reason                    = $this->local_record_removal_blocking_reason( $site, $non_terminal_action_count );

		return array(
			'eligible'                  => '' === $reason,
			'reason'                    => '' === $reason ? __( 'Archived, terminal, and credential-free. A future removal workflow could safely present a separate confirmation gate for this dashboard-local record.', 'alynt-drime-backups-dashboard' ) : $reason,
			'snapshot_count'            => $snapshot_count,
			'action_count'              => $action_count,
			'non_terminal_action_count' => $non_terminal_action_count,
		);
	}

	/**
	 * Gets the first reason an archived record is not ready for a future removal workflow.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @param int                 $non_terminal_action_count Non-terminal remote action rows.
	 * @return string
	 */
	private function local_record_removal_blocking_reason( array $site, $non_terminal_action_count ) {
		$status = isset( $site['enrollment_status'] ) ? sanitize_key( (string) $site['enrollment_status'] ) : '';

		if ( empty( $site['archived_at'] ) ) {
			return __( 'Only archived records can be evaluated for future local removal.', 'alynt-drime-backups-dashboard' );
		}

		if ( 'revoked' !== $status && ! $this->is_expired_pending_local_record( $site ) ) {
			return __( 'Only revoked records and expired pending records can be considered for future local removal.', 'alynt-drime-backups-dashboard' );
		}

		if ( $this->site_has_polling_credentials( $site ) ) {
			return __( 'Polling credentials are still present. Revoke the record before considering future local removal.', 'alynt-drime-backups-dashboard' );
		}

		if ( ! empty( $site['action_key_id'] ) || ! empty( $site['action_private_key_ciphertext'] ) ) {
			return __( 'Remote-action signing credentials are still present. Revocation must clear them before local removal can be considered.', 'alynt-drime-backups-dashboard' );
		}

		if ( ! empty( $site['next_poll_at'] ) ) {
			return __( 'A next poll time is still stored. Future removal should require the record to be fully unscheduled.', 'alynt-drime-backups-dashboard' );
		}

		if ( ! empty( $site['paused_at'] ) ) {
			return __( 'The record is paused rather than fully terminal. Revoke or expire it before local removal can be considered.', 'alynt-drime-backups-dashboard' );
		}

		if ( $non_terminal_action_count > 0 ) {
			return __( 'One or more retained action rows are still non-terminal. Future removal should wait until action history is terminal.', 'alynt-drime-backups-dashboard' );
		}

		return '';
	}

	/**
	 * Determines whether a site is an expired pending local record.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return bool
	 */
	private function is_expired_pending_local_record( array $site ) {
		$status = isset( $site['enrollment_status'] ) ? sanitize_key( (string) $site['enrollment_status'] ) : '';

		if ( 'pending' !== $status ) {
			return false;
		}

		$expires_at = isset( $site['pairing_expires_at'] ) ? (string) $site['pairing_expires_at'] : '';

		if ( '' === $expires_at ) {
			return true;
		}

		$expires = strtotime( $expires_at );

		return false === $expires || $expires <= time();
	}

	/**
	 * Counts rows through an optional repository method.
	 *
	 * @param string $property Repository property name.
	 * @param string $method Repository method name.
	 * @param int    $site_id Site ID.
	 * @return int
	 */
	private function count_repository_rows_for_site( $property, $method, $site_id ) {
		if ( ! isset( $this->{$property} ) || ! is_object( $this->{$property} ) || ! method_exists( $this->{$property}, $method ) ) {
			return 0;
		}

		return max( 0, (int) $this->{$property}->{$method}( $site_id ) );
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
