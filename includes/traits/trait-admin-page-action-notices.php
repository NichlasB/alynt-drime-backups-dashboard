<?php
/**
 * Admin page action notices.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.26
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders local and remote action notices for the admin page.
 *
 * @since 0.1.26
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Action_Notices {
	/**
	 * Renders an action result notice.
	 *
	 * @param array<string,mixed>|WP_Error|null $result Result.
	 * @return void
	 */
	private function render_action_result( $result ) {
		if ( null === $result ) {
			return;
		}

		if ( is_wp_error( $result ) ) {
			$this->render_action_notice( $result->get_error_message(), 'notice-error' );
			return;
		}

		if ( isset( $result['pairing_token'] ) ) {
			$this->render_action_notice( __( 'Pending dashboard site created. Copy the pairing token now; it is not stored and cannot be shown again.', 'alynt-drime-backups-dashboard' ), 'notice-success' );
			return;
		}

		if ( isset( $result['action'] ) && 'revoke_local' === $result['action'] ) {
			$message = ! empty( $result['success'] )
				? __( 'Dashboard record revoked locally. No client site or Drime action was attempted.', 'alynt-drime-backups-dashboard' )
				: __( 'The dashboard record could not be revoked locally. Refresh the site detail screen and try again; the record may already have changed.', 'alynt-drime-backups-dashboard' );
			$class   = ! empty( $result['success'] ) ? 'notice-success' : 'notice-error';

			$this->render_action_notice( $message, $class );
			return;
		}

		if ( isset( $result['action'] ) && 'archive_local' === $result['action'] ) {
			$message = ! empty( $result['success'] )
				? __( 'Dashboard record archived locally. It is hidden from the default Sites view and no client site or Drime action was attempted.', 'alynt-drime-backups-dashboard' )
				: __( 'The dashboard record could not be archived locally. Refresh the site detail screen and try again; the record may already have changed.', 'alynt-drime-backups-dashboard' );
			$class   = ! empty( $result['success'] ) ? 'notice-success' : 'notice-error';
			$this->render_action_notice( $message, $class );
			return;
		}

		if ( isset( $result['action'] ) && 'unarchive_local' === $result['action'] ) {
			$message = ! empty( $result['success'] )
				? __( 'Dashboard record unarchived locally. It is visible again in dashboard history; credentials and polling were not restored.', 'alynt-drime-backups-dashboard' )
				: __( 'The dashboard record could not be unarchived locally. Refresh the site detail screen and try again; the record may already have changed.', 'alynt-drime-backups-dashboard' );
			$class   = ! empty( $result['success'] ) ? 'notice-success' : 'notice-error';
			$this->render_action_notice( $message, $class );
			return;
		}

		if ( isset( $result['action'] ) && 'check_status_now' === $result['action'] ) {
			$this->render_action_notice( __( 'Read-only status check completed and stored. No backup or client-site setting was changed.', 'alynt-drime-backups-dashboard' ), 'notice-success' );
			return;
		}

		if ( isset( $result['action'] ) && 'pause_polling' === $result['action'] ) {
			$message = ! empty( $result['success'] )
				? __( 'Scheduled polling paused locally. Manual Check Now remains available when polling credentials exist, and no client site or Drime action was attempted.', 'alynt-drime-backups-dashboard' )
				: __( 'Scheduled polling could not be paused locally. Refresh the site detail screen and try again; the record may already have changed.', 'alynt-drime-backups-dashboard' );
			$class   = ! empty( $result['success'] ) ? 'notice-success' : 'notice-error';

			$this->render_action_notice( $message, $class );
			return;
		}

		if ( isset( $result['action'] ) && 'resume_polling' === $result['action'] ) {
			$message = ! empty( $result['success'] )
				? __( 'Scheduled polling resumed locally. The next scheduled poll is due as soon as WordPress cron runs.', 'alynt-drime-backups-dashboard' )
				: __( 'Scheduled polling could not be resumed locally. Refresh the site detail screen and try again; the record may already have changed.', 'alynt-drime-backups-dashboard' );
			$class   = ! empty( $result['success'] ) ? 'notice-success' : 'notice-error';

			$this->render_action_notice( $message, $class );
			return;
		}

		if ( isset( $result['action'] ) && 'generate_action_opt_in_token' === $result['action'] ) {
			$this->render_action_notice( __( 'V2 action opt-in token generated. Copy it now; the token is not stored and cannot be shown again.', 'alynt-drime-backups-dashboard' ), 'notice-success' );
			return;
		}

		if ( isset( $result['action'] ) && 'request_backup_now' === $result['action'] ) {
			$this->render_remote_action_notice(
				$result,
				__( 'Request Backup Now was accepted by the client site, and a read-only status check was completed.', 'alynt-drime-backups-dashboard' ),
				__( 'Request Backup Now was accepted by the client site. Wait briefly, then use Check Now to confirm the latest reported result.', 'alynt-drime-backups-dashboard' ),
				__( 'The client site did not accept the remote action request.', 'alynt-drime-backups-dashboard' ),
				__( 'Request Backup Now could not be completed. Review the remote action history for this site.', 'alynt-drime-backups-dashboard' )
			);
			return;
		}

		if ( isset( $result['action'] ) && 'schedule_preview' === $result['action'] ) {
			$this->render_remote_action_notice(
				$result,
				__( 'Schedule preview was accepted by the client site. No schedule was changed. Wait briefly, then use Check Now to see the preview result reported by the client.', 'alynt-drime-backups-dashboard' ),
				__( 'Schedule preview was accepted by the client site. No schedule was changed. Wait briefly, then use Check Now to see the preview result reported by the client.', 'alynt-drime-backups-dashboard' ),
				__( 'The client site did not accept the schedule preview request.', 'alynt-drime-backups-dashboard' ),
				__( 'Schedule preview could not be completed. Review the remote action history for this site.', 'alynt-drime-backups-dashboard' )
			);
			return;
		}

		if ( isset( $result['action'] ) && 'schedule_apply' === $result['action'] ) {
			$this->render_remote_action_notice(
				$result,
				__( 'Schedule Apply was accepted by the client site. It changes only future Alynt scan/upload timing. Use Check Now to confirm the applied cadence reported by the client.', 'alynt-drime-backups-dashboard' ),
				__( 'Schedule Apply was accepted by the client site. It changes only future Alynt scan/upload timing. Use Check Now to confirm the applied cadence reported by the client.', 'alynt-drime-backups-dashboard' ),
				__( 'The client site did not accept the Schedule Apply request.', 'alynt-drime-backups-dashboard' ),
				__( 'Schedule Apply could not be completed. Review the remote action history for this site.', 'alynt-drime-backups-dashboard' )
			);
			return;
		}

		if ( isset( $result['action'] ) && 'update_source_policy' === $result['action'] ) {
			$message = ! empty( $result['success'] )
				? __( 'Backup-source monitoring policy saved. This changes dashboard classification only; no client site, backup, or Drime data was changed.', 'alynt-drime-backups-dashboard' )
				: __( 'Backup-source monitoring policy could not be saved. Refresh the site detail screen and try again.', 'alynt-drime-backups-dashboard' );
			$class   = ! empty( $result['success'] ) ? 'notice-success' : 'notice-error';

			$this->render_action_notice( $message, $class );
			return;
		}

		if ( isset( $result['action'] ) && 'update_diagnostics_settings' === $result['action'] ) {
			$message = ! empty( $result['success'] )
				? __( 'Diagnostics settings saved.', 'alynt-drime-backups-dashboard' )
				: __( 'Diagnostics settings could not be saved. Refresh the page and try again; if it continues, check that WordPress options can be updated.', 'alynt-drime-backups-dashboard' );
			$class   = ! empty( $result['success'] ) ? 'notice-success' : 'notice-error';

			$this->render_action_notice( $message, $class );
			return;
		}

		if ( isset( $result['action'] ) && 'clear_diagnostics_events' === $result['action'] ) {
			$message = ! empty( $result['success'] )
				? __( 'Diagnostics events cleared.', 'alynt-drime-backups-dashboard' )
				: __( 'Diagnostics events could not be cleared. Refresh the Diagnostics screen and try again; the retained event buffer may already have changed.', 'alynt-drime-backups-dashboard' );
			$class   = ! empty( $result['success'] ) ? 'notice-success' : 'notice-error';

			$this->render_action_notice( $message, $class );
		}
	}

	/**
	 * Renders a remote-action result notice.
	 *
	 * @param array<string,mixed> $result Accepted result array.
	 * @param string              $success_with_poll Notice when accepted and poll followed.
	 * @param string              $success_without_poll Notice when accepted without follow-up poll.
	 * @param string              $warning_fallback Warning fallback.
	 * @param string              $error_message Error message.
	 * @return void
	 */
	private function render_remote_action_notice( array $result, $success_with_poll, $success_without_poll, $warning_fallback, $error_message ) {
		$remote_state = isset( $result['remote_state'] ) ? sanitize_key( (string) $result['remote_state'] ) : '';

		if ( in_array( $remote_state, array( 'accepted', 'running', 'succeeded' ), true ) ) {
			$message = ! empty( $result['poll_after_dispatch'] ) ? $success_with_poll : $success_without_poll;
			$this->render_action_notice( $message, 'notice-success' );
			return;
		}

		if ( in_array( $remote_state, array( 'rate_limited', 'busy', 'rejected', 'unsupported' ), true ) ) {
			$this->render_action_notice( isset( $result['result_summary'] ) ? (string) $result['result_summary'] : $warning_fallback, 'notice-warning' );
			return;
		}

		$this->render_action_notice( $error_message, 'notice-error' );
	}

	/**
	 * Renders a submitted-action notice with an explicit live-region role.
	 *
	 * @param string $message Notice message.
	 * @param string $notice_class WordPress notice tone class.
	 * @return void
	 */
	private function render_action_notice( $message, $notice_class ) {
		$is_error = 'notice-error' === $notice_class;

		printf(
			'<div id="adbd-action-notice" class="notice %1$s is-dismissible inline" role="%2$s" aria-live="%3$s"><p>%4$s</p></div>',
			esc_attr( $notice_class ),
			$is_error ? 'alert' : 'status',
			$is_error ? 'assertive' : 'polite',
			esc_html( $message )
		);
	}
}
