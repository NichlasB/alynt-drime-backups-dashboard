<?php
/**
 * Admin page cleanup-preview helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.51
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders preview-only cleanup controls for opted-in client sites.
 *
 * @since 0.1.51
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Cleanup_Preview_Helpers {
	/**
	 * Renders the Cleanup Preview panel when supported by the latest client report.
	 *
	 * @param array<string,mixed>      $site Site row.
	 * @param array<string,mixed>|null $snapshot Latest snapshot row.
	 * @return void
	 */
	private function render_cleanup_preview_panel( array $site, $snapshot ) {
		$payload      = is_array( $snapshot ) ? $this->decoded_snapshot_payload( $snapshot ) : array();
		$availability = $this->cleanup_preview_availability( $site, $payload );

		if ( ! $availability['reported'] ) {
			return;
		}

		echo '<div class="adbd-panel"><h3>' . esc_html__( 'Cleanup Preview', 'alynt-drime-backups-dashboard' ) . '</h3><div class="adbd-panel-body">';
		echo '<p>' . esc_html__( 'V2.4 Cleanup Preview asks the client uploader to estimate safe, uploader-owned temporary artifacts only. It does not delete files, clean Drime, change retention, restore backups, or expose filesystem paths.', 'alynt-drime-backups-dashboard' ) . '</p>';
		echo '<p class="description">' . esc_html__( 'Cleanup apply is intentionally unavailable in this release. Preview results are evidence only; the dashboard cannot use them to delete files or mutate Drime.', 'alynt-drime-backups-dashboard' ) . '</p>';

		if ( $availability['available'] ) {
			echo '<p><span class="adbd-status-pill is-working">' . esc_html__( 'Capability reported', 'alynt-drime-backups-dashboard' ) . '</span> ' . esc_html( $availability['message'] ) . '</p>';
			$this->render_cleanup_preview_form( $site );
		} else {
			echo '<p><span class="adbd-status-pill is-pending">' . esc_html__( 'Not available yet', 'alynt-drime-backups-dashboard' ) . '</span> ' . esc_html( $availability['message'] ) . '</p>';
		}

		echo '</div></div>';
	}

	/**
	 * Renders the signed cleanup-preview form.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @return void
	 */
	private function render_cleanup_preview_form( array $site ) {
		$description_id = 'adbd-cleanup-preview-description';
		?>
		<form method="post" class="adbd-inline-form">
			<?php wp_nonce_field( 'alynt_drime_backups_dashboard_cleanup_preview' ); ?>
			<input type="hidden" name="alynt_drime_backups_dashboard_action" value="cleanup_preview">
			<input type="hidden" name="dashboard_site_id" value="<?php echo esc_attr( isset( $site['id'] ) ? (string) (int) $site['id'] : '0' ); ?>">
			<button type="submit" class="button button-secondary" data-busy-label="<?php esc_attr_e( 'Previewing…', 'alynt-drime-backups-dashboard' ); ?>" aria-describedby="<?php echo esc_attr( $description_id ); ?>"><?php esc_html_e( 'Preview Cleanup', 'alynt-drime-backups-dashboard' ); ?></button>
			<span id="<?php echo esc_attr( $description_id ); ?>" class="description"><?php esc_html_e( 'Sends one signed preview-only intent for uploader-owned temporary artifacts. The client may accept, reject, rate-limit, or report busy; no cleanup or delete action is requested.', 'alynt-drime-backups-dashboard' ); ?></span>
		</form>
		<?php
	}

	/**
	 * Gets cleanup-preview availability from redacted client evidence.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @param array<string,mixed> $payload Latest decoded snapshot payload.
	 * @return array{reported:bool,available:bool,message:string}
	 */
	private function cleanup_preview_availability( array $site, array $payload ) {
		$remote_actions = isset( $payload['remote_actions'] ) && is_array( $payload['remote_actions'] ) ? $payload['remote_actions'] : array();
		$capabilities   = new Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities();
		$clean          = $capabilities->sanitize( $remote_actions );

		if ( is_wp_error( $clean ) || empty( $clean['cleanup_management'] ) || ! is_array( $clean['cleanup_management'] ) ) {
			return array(
				'reported'  => false,
				'available' => false,
				'message'   => '',
			);
		}

		if ( ! $this->site_can_manual_check( $site ) ) {
			return array(
				'reported'  => true,
				'available' => false,
				'message'   => __( 'This site must be actively enrolled with polling credentials before V2 cleanup preview can be considered.', 'alynt-drime-backups-dashboard' ),
			);
		}

		if ( $capabilities->supports_cleanup_preview_action( $clean ) ) {
			if ( array_key_exists( 'action_key_id', $site ) && ( empty( $site['action_key_id'] ) || empty( $site['action_private_key_ciphertext'] ) ) ) {
				return array(
					'reported'  => true,
					'available' => false,
					'message'   => __( 'The latest client report says cleanup preview is available, but this dashboard record no longer has the encrypted signing key. Regenerate the V2 opt-in token and run Check Now.', 'alynt-drime-backups-dashboard' ),
				);
			}

			return array(
				'reported'  => true,
				'available' => true,
				'message'   => __( 'The latest client report says cleanup preview is available for uploader-owned temporary artifacts. This is preview-only evidence; cleanup apply remains unavailable.', 'alynt-drime-backups-dashboard' ),
			);
		}

		return array(
			'reported'  => true,
			'available' => false,
			'message'   => __( 'The latest client report includes cleanup metadata, but preview is not enabled under the dashboard allowlist. Run Check Now after updating the client opt-in.', 'alynt-drime-backups-dashboard' ),
		);
	}
}
