<?php
/**
 * Admin page polling detail helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides polling and manual status-check rendering helpers for dashboard admin screens.
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Polling_Detail_Helpers {
	/**
	 * Determines whether a site row has active polling credentials.
	 *
	 * Sites-list queries intentionally expose only a redacted
	 * `has_polling_secret` flag, while detail queries may include the encrypted
	 * ciphertext. Treat either as sufficient evidence without exposing the
	 * ciphertext in list screens.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return bool
	 */
	private function site_has_polling_credentials( array $site ) {
		$has_secret = ! empty( $site['polling_secret_ciphertext'] ) || ! empty( $site['has_polling_secret'] );

		return ! empty( $site['polling_key_id'] ) && $has_secret;
	}

	/**
	 * Determines whether scheduled polling is paused locally for a site.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return bool
	 */
	private function site_polling_is_paused( array $site ) {
		return ! empty( $site['paused_at'] );
	}

	/**
	 * Determines whether a site can expose the local pause/resume control.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return bool
	 */
	private function site_can_toggle_scheduled_polling( array $site ) {
		$status = isset( $site['enrollment_status'] ) ? sanitize_key( $site['enrollment_status'] ) : '';

		if ( ! empty( $site['archived_at'] ) ) {
			return false;
		}

		if ( in_array( $status, array( 'pending', 'revoked' ), true ) ) {
			return false;
		}

		return $this->site_has_polling_credentials( $site );
	}

	/**
	 * Determines whether a site can be manually checked now.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return bool
	 */
	private function site_can_manual_check( array $site ) {
		if ( ! empty( $site['archived_at'] ) ) {
			return false;
		}

		if ( isset( $site['enrollment_status'] ) && 'revoked' === $site['enrollment_status'] ) {
			return false;
		}

		return $this->site_has_polling_credentials( $site );
	}

	/**
	 * Gets state-specific unavailable copy for manual checks.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return string
	 */
	private function manual_check_unavailable_message( array $site ) {
		$status = isset( $site['enrollment_status'] ) ? sanitize_key( $site['enrollment_status'] ) : '';

		if ( ! empty( $site['archived_at'] ) ) {
			return __( 'Record archived locally. Unarchive it before manual checks are available.', 'alynt-drime-backups-dashboard' );
		}

		if ( 'revoked' === $status ) {
			return __( 'Pairing revoked locally. Re-enroll this site before manual checks are available.', 'alynt-drime-backups-dashboard' );
		}

		if ( 'pending' === $status ) {
			return __( 'Waiting for client opt-in before manual checks are available.', 'alynt-drime-backups-dashboard' );
		}

		return __( 'Polling credentials are missing. Re-enroll this site to restore manual checks.', 'alynt-drime-backups-dashboard' );
	}

	/**
	 * Renders the Sites-list next-poll line with credential-aware copy.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return string
	 */
	private function next_poll_html( array $site ) {
		if ( $this->site_polling_is_paused( $site ) ) {
			return '<span class="adbd-row-meta adbd-row-meta-muted">' . esc_html__( 'Next poll:', 'alynt-drime-backups-dashboard' ) . ' ' . esc_html__( 'Paused locally', 'alynt-drime-backups-dashboard' ) . '</span>';
		}

		if ( $this->site_can_manual_check( $site ) ) {
			return '<span class="adbd-row-meta">' . esc_html__( 'Next poll:', 'alynt-drime-backups-dashboard' ) . ' ' . $this->time_html( isset( $site['next_poll_at'] ) ? $site['next_poll_at'] : '' ) . '</span>';
		}

		$status = isset( $site['enrollment_status'] ) ? sanitize_key( $site['enrollment_status'] ) : '';

		if ( ! empty( $site['archived_at'] ) ) {
			$message = __( 'Archived locally', 'alynt-drime-backups-dashboard' );
		} elseif ( 'revoked' === $status ) {
			$message = __( 'Unavailable until re-enrolled', 'alynt-drime-backups-dashboard' );
		} elseif ( 'pending' === $status ) {
			$message = __( 'Waiting for client opt-in', 'alynt-drime-backups-dashboard' );
		} else {
			$message = __( 'Credentials missing', 'alynt-drime-backups-dashboard' );
		}

		return '<span class="adbd-row-meta adbd-row-meta-muted">' . esc_html__( 'Next poll:', 'alynt-drime-backups-dashboard' ) . ' ' . esc_html( $message ) . '</span>';
	}

	/**
	 * Renders a manual read-only status-check form when credentials exist.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @param int                 $site_id Site ID.
	 * @param bool                $primary Whether to use primary styling.
	 * @return void
	 */
	private function render_check_status_form( array $site, $site_id, $primary ) {
		if ( ! $this->site_can_manual_check( $site ) ) {
			echo '<span class="description adbd-action-unavailable">' . esc_html( $this->manual_check_unavailable_message( $site ) ) . '</span>';
			return;
		}

		?>
		<form method="post" class="adbd-inline-form">
			<?php wp_nonce_field( 'alynt_drime_backups_dashboard_check_status_now' ); ?>
			<input type="hidden" name="alynt_drime_backups_dashboard_action" value="check_status_now">
			<input type="hidden" name="dashboard_site_id" value="<?php echo esc_attr( (string) $site_id ); ?>">
			<button type="submit" class="button <?php echo $primary ? 'button-primary' : ''; ?>" data-busy-label="<?php esc_attr_e( 'Checking…', 'alynt-drime-backups-dashboard' ); ?>"><?php esc_html_e( 'Check Now', 'alynt-drime-backups-dashboard' ); ?></button>
		</form>
		<?php
	}

	/**
	 * Renders a dashboard-local scheduled polling pause/resume form.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @param int                 $site_id Site ID.
	 * @return void
	 */
	private function render_polling_pause_form( array $site, $site_id ) {
		if ( ! $this->site_can_toggle_scheduled_polling( $site ) ) {
			return;
		}

		$is_paused    = $this->site_polling_is_paused( $site );
		$action       = $is_paused ? 'resume_polling' : 'pause_polling';
		$nonce_action = $is_paused ? 'alynt_drime_backups_dashboard_resume_polling' : 'alynt_drime_backups_dashboard_pause_polling';
		$button_text  = $is_paused ? __( 'Resume Polling', 'alynt-drime-backups-dashboard' ) : __( 'Pause Polling', 'alynt-drime-backups-dashboard' );
		$busy_text    = $is_paused ? __( 'Resuming…', 'alynt-drime-backups-dashboard' ) : __( 'Pausing…', 'alynt-drime-backups-dashboard' );
		$description  = $is_paused
			? __( 'Resume scheduled read-only polling for this dashboard record.', 'alynt-drime-backups-dashboard' )
			: __( 'Pause scheduled read-only polling for this dashboard record. Manual Check Now remains available.', 'alynt-drime-backups-dashboard' );

		?>
		<form method="post" class="adbd-inline-form">
			<?php wp_nonce_field( $nonce_action ); ?>
			<input type="hidden" name="alynt_drime_backups_dashboard_action" value="<?php echo esc_attr( $action ); ?>">
			<input type="hidden" name="dashboard_site_id" value="<?php echo esc_attr( (string) $site_id ); ?>">
			<button type="submit" class="button" aria-label="<?php echo esc_attr( $description ); ?>" data-busy-label="<?php echo esc_attr( $busy_text ); ?>"><?php echo esc_html( $button_text ); ?></button>
		</form>
		<?php
	}

	/**
	 * Renders the Site Detail scheduled polling control panel.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return void
	 */
	private function render_polling_pause_panel( array $site ) {
		$site_id = isset( $site['id'] ) ? absint( $site['id'] ) : 0;

		echo '<div class="adbd-panel"><h3>' . esc_html__( 'Scheduled Polling Control', 'alynt-drime-backups-dashboard' ) . '</h3><div class="adbd-panel-body">';
		echo '<p>' . esc_html__( 'This control changes only this dashboard record. It does not contact the client site, start or stop a backup, change settings, delete data, or mutate Drime.', 'alynt-drime-backups-dashboard' ) . '</p>';
		echo '<dl class="adbd-detail-list">';
		$this->render_detail_item( __( 'Scheduled polling', 'alynt-drime-backups-dashboard' ), $this->site_polling_is_paused( $site ) ? __( 'Paused locally', 'alynt-drime-backups-dashboard' ) : __( 'Enabled', 'alynt-drime-backups-dashboard' ) );
		$this->render_detail_item( __( 'Paused since', 'alynt-drime-backups-dashboard' ), $this->time_html( isset( $site['paused_at'] ) ? $site['paused_at'] : '' ), true );
		echo '</dl>';

		if ( $this->site_can_toggle_scheduled_polling( $site ) ) {
			echo '<div class="adbd-actions">';
			$this->render_polling_pause_form( $site, $site_id );
			echo '<span class="description">' . esc_html__( 'Use this when a site is intentionally offline, under maintenance, or should stop scheduled dashboard polling temporarily.', 'alynt-drime-backups-dashboard' ) . '</span></div>';
		} else {
			echo '<p class="description">' . esc_html__( 'Pause and resume are available only for non-revoked enrolled records with active polling credentials.', 'alynt-drime-backups-dashboard' ) . '</p>';
		}

		echo '</div></div>';
	}
}
