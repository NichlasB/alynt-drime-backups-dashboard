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

	/**
	 * Gets the latest successful apply public ID that may support rollback preview.
	 *
	 * @param int                                 $site_id Site ID.
	 * @param string                              $schedule_id Schedule ID.
	 * @param array<int,array<string,mixed>>|null $remote_action_history Recent remote action rows, or null to fetch.
	 * @return string
	 */
	private function latest_apply_public_id_for_rollback_preview( $site_id, $schedule_id, $remote_action_history = null ) {
		$site_id     = absint( $site_id );
		$schedule_id = sanitize_key( (string) $schedule_id );

		if (
			0 === $site_id
			|| '' === $schedule_id
			|| ! property_exists( $this, 'remote_actions' )
			|| ! $this->remote_actions instanceof Alynt_Drime_Backups_Dashboard_Remote_Action_Repository
		) {
			return '';
		}

		$history = is_array( $remote_action_history ) ? $remote_action_history : $this->remote_actions->recent_for_site( $site_id, 10 );

		foreach ( $history as $row ) {
			if (
				! is_array( $row )
				|| Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCHEDULE_APPLY !== ( isset( $row['action_type'] ) ? sanitize_key( (string) $row['action_type'] ) : '' )
				|| 'succeeded' !== ( isset( $row['state'] ) ? sanitize_key( (string) $row['state'] ) : '' )
				|| empty( $row['public_id'] )
			) {
				continue;
			}

			$context  = ! empty( $row['redacted_context_json'] ) ? json_decode( (string) $row['redacted_context_json'], true ) : array();
			$apply    = is_array( $context ) && isset( $context['schedule_apply'] ) && is_array( $context['schedule_apply'] ) ? $context['schedule_apply'] : array();
			$metadata = isset( $apply['rollback_metadata'] ) && is_array( $apply['rollback_metadata'] ) ? $apply['rollback_metadata'] : array();

			if ( ! empty( $apply ) && ! empty( $apply['schedule_id'] ) && sanitize_key( (string) $apply['schedule_id'] ) !== $schedule_id ) {
				continue;
			}

			if ( ! empty( $metadata ) && empty( $metadata['captured'] ) ) {
				continue;
			}

			return (string) $row['public_id'];
		}

		return '';
	}
}
