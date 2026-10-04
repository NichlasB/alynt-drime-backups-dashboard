<?php
/**
 * Admin page schedule rollback-preview helper split.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.43
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides guarded schedule rollback-preview form rendering helpers.
 *
 * @since 0.1.43
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Schedule_Rollback_Preview_Helpers {
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Schedule_Rollback_Preview_Evidence;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Schedule_Rollback_Preview_Readiness;

	/**
	 * Renders the guarded V2.3 schedule-rollback-preview form when rollback metadata exists.
	 *
	 * @param array<string,mixed>            $site Site row.
	 * @param array<string,mixed>            $schedule Schedule summary.
	 * @param array<string,mixed>            $capabilities Sanitized capabilities.
	 * @param array<int,array<string,mixed>> $remote_action_history Recent remote action rows.
	 * @return void
	 */
	private function render_schedule_rollback_preview_form( array $site, array $schedule, array $capabilities, array $remote_action_history = array() ) {
		$site_id             = isset( $site['id'] ) ? absint( $site['id'] ) : 0;
		$schedule_id         = isset( $schedule['schedule_id'] ) ? sanitize_key( (string) $schedule['schedule_id'] ) : '';
		$capabilities_helper = new Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities();

		if (
			0 === $site_id
			|| Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::SCHEDULE_SCAN_UPLOAD !== $schedule_id
			|| ! $capabilities_helper->supports_schedule_rollback_preview_action( $capabilities, $schedule_id )
			|| ! property_exists( $this, 'remote_actions' )
			|| ! $this->remote_actions instanceof Alynt_Drime_Backups_Dashboard_Remote_Action_Repository
		) {
			return;
		}

		$rollback_preview = $this->remote_actions->successful_schedule_apply_for_rollback_preview(
			$site_id,
			$this->latest_apply_public_id_for_rollback_preview( $site_id, $schedule_id, $remote_action_history ),
			$capabilities
		);

		if ( is_wp_error( $rollback_preview ) ) {
			echo '<p class="description">' . esc_html__( 'Rollback preview becomes available after a successful Schedule Apply with unexpired rollback metadata and explicit client rollback-preview support. No rollback execution is available.', 'alynt-drime-backups-dashboard' ) . '</p>';
			return;
		}

		$description_id = 'adbd-schedule-rollback-preview-description-' . $schedule_id;
		?>
		<form method="post" class="adbd-inline-form adbd-schedule-rollback-preview-form">
			<?php wp_nonce_field( 'alynt_drime_backups_dashboard_preview_schedule_rollback' ); ?>
			<input type="hidden" name="alynt_drime_backups_dashboard_action" value="preview_schedule_rollback">
			<input type="hidden" name="dashboard_site_id" value="<?php echo esc_attr( (string) $site_id ); ?>">
			<input type="hidden" name="source_apply_action_id" value="<?php echo esc_attr( (string) $rollback_preview['source_apply_action_id'] ); ?>">
			<p id="<?php echo esc_attr( $description_id ); ?>" class="description">
				<?php
				echo esc_html(
					sprintf(
						/* translators: 1: applied cadence, 2: rollback cadence. */
						__( 'Asks the client to preview reverting the Alynt uploader scan cadence from %1$s back to %2$s. This is non-mutating: it does not change schedules, start backups, restore, delete, clean up, alter Drime, or change credentials.', 'alynt-drime-backups-dashboard' ),
						$this->schedule_cadence_label( isset( $rollback_preview['applied_cadence'] ) ? (string) $rollback_preview['applied_cadence'] : '' ),
						$this->schedule_cadence_label( isset( $rollback_preview['previous_cadence'] ) ? (string) $rollback_preview['previous_cadence'] : '' )
					)
				);
				?>
			</p>
			<label>
				<input type="checkbox" name="schedule_rollback_preview_confirm" value="1" aria-describedby="<?php echo esc_attr( $description_id ); ?>">
				<?php esc_html_e( 'I understand this only previews rollback readiness and does not execute a rollback or change any schedule.', 'alynt-drime-backups-dashboard' ); ?>
			</label>
			<button type="submit" class="button" data-busy-label="<?php esc_attr_e( 'Previewing…', 'alynt-drime-backups-dashboard' ); ?>"><?php esc_html_e( 'Preview Rollback', 'alynt-drime-backups-dashboard' ); ?></button>
		</form>
		<?php
	}
}
