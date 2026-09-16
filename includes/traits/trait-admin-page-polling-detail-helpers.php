<?php
/**
 * Admin page
polling detail helpers
.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *
Provides polling and manual status-check rendering helpers for dashboard admin screens.
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
	 * Determines whether a site can be manually checked now.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return bool
	 */
	private function site_can_manual_check( array $site ) {
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
		if ( $this->site_can_manual_check( $site ) ) {
			return '<span class="adbd-row-meta">' . esc_html__( 'Next poll:', 'alynt-drime-backups-dashboard' ) . ' ' . $this->time_html( isset( $site['next_poll_at'] ) ? $site['next_poll_at'] : '' ) . '</span>';
		}

		$status = isset( $site['enrollment_status'] ) ? sanitize_key( $site['enrollment_status'] ) : '';

		if ( 'revoked' === $status ) {
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
}
