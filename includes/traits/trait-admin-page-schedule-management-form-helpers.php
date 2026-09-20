<?php
/**
 * Admin page helper split.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *
Provides remote schedule management form and panel rendering helpers.
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Schedule_Management_Form_Helpers {


	/**
	 * Renders preview-only schedule-management capability reported by the client.
	 *
	 * @param array<string,mixed>|null       $snapshot Latest snapshot row.
	 * @param array<string,mixed>            $site Site row.
	 * @param array<int,array<string,mixed>> $remote_action_history Recent remote action rows.
	 * @return void
	 */
	private function render_schedule_management_panel( $snapshot, array $site = array(), array $remote_action_history = array() ) {
		$payload              = is_array( $snapshot ) ? $this->decoded_snapshot_payload( $snapshot ) : array();
		$schedule_management  = $this->schedule_management_summary( $payload );
		$capabilities         = new Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities();
		$remote_actions       = $this->remote_actions_from_payload( $payload );
		$clean_capabilities   = $capabilities->sanitize( $remote_actions );
		$clean_capabilities   = is_wp_error( $clean_capabilities ) ? array() : $clean_capabilities;
		$preview_is_supported = $capabilities->supports_schedule_management_preview( $clean_capabilities );

		echo '<div class="adbd-panel"><h3>' . esc_html__( 'Schedule Management', 'alynt-drime-backups-dashboard' ) . '</h3><div class="adbd-panel-body">';
		echo '<p>' . esc_html__( 'V2.3 schedule controls are limited to the Alynt uploader scan cadence reported by the client as Alynt scan/upload. Preview is non-mutating. Apply requires a fresh matching preview, client-side Schedule Apply opt-in, and a signed request; it does not create backups, change upload-worker, WPvivid, or server-runner schedules, alter Drime, delete, clean up, restore, roll back schedules, or change credentials.', 'alynt-drime-backups-dashboard' ) . '</p>';

		if ( ! $preview_is_supported ) {
			echo '<p><span class="adbd-status-pill is-pending">' . esc_html__( 'Not reported yet', 'alynt-drime-backups-dashboard' ) . '</span> ' . esc_html__( 'The latest client snapshot does not advertise preview-only schedule capability. Upgrade and poll the client before schedule posture can be shown here.', 'alynt-drime-backups-dashboard' ) . '</p>';
			echo '</div></div>';
			return;
		}

		if ( ! empty( $clean_capabilities['schedule_management']['apply_supported'] ) ) {
			echo '<p><span class="adbd-status-pill is-working">' . esc_html__( 'Apply available after preview', 'alynt-drime-backups-dashboard' ) . '</span> ' . esc_html__( 'The client reports guarded Schedule Apply support for this schedule class.', 'alynt-drime-backups-dashboard' ) . '</p>';
		} else {
			echo '<p><span class="adbd-status-pill is-working">' . esc_html__( 'Preview only', 'alynt-drime-backups-dashboard' ) . '</span> ' . esc_html__( 'Schedule capability is reported for operator review only.', 'alynt-drime-backups-dashboard' ) . '</p>';
		}

		foreach ( $schedule_management['schedules'] as $schedule ) {
			if ( ! is_array( $schedule ) ) {
				continue;
			}

			echo '<section class="adbd-source-card adbd-schedule-card" aria-label="' . esc_attr( $this->schedule_label( $schedule ) ) . '">';
			echo '<h4>' . esc_html( $this->schedule_label( $schedule ) ) . '</h4>';
			echo '<dl class="adbd-detail-list">';
			$this->render_detail_item( __( 'Owner', 'alynt-drime-backups-dashboard' ), $this->schedule_owner_label( isset( $schedule['owner'] ) ? (string) $schedule['owner'] : '' ) );
			$this->render_detail_item( __( 'Current cadence', 'alynt-drime-backups-dashboard' ), $this->schedule_cadence_label( isset( $schedule['current_cadence'] ) ? (string) $schedule['current_cadence'] : '' ) );
			$this->render_detail_item( __( 'Current interval', 'alynt-drime-backups-dashboard' ), $this->schedule_interval_label( isset( $schedule['current_interval_seconds'] ) ? (int) $schedule['current_interval_seconds'] : 0 ) );
			$this->render_detail_item( __( 'Next run', 'alynt-drime-backups-dashboard' ), $this->time_html( isset( $schedule['current_next_run_at'] ) ? (string) $schedule['current_next_run_at'] : '' ), true );
			$this->render_detail_item( __( 'Supported cadences', 'alynt-drime-backups-dashboard' ), $this->schedule_cadences_label( isset( $schedule['supported_cadences'] ) ? $schedule['supported_cadences'] : array() ) );
			$this->render_detail_item( __( 'Minimum interval', 'alynt-drime-backups-dashboard' ), $this->schedule_interval_label( isset( $schedule['minimum_interval_seconds'] ) ? (int) $schedule['minimum_interval_seconds'] : 0 ) );
			$this->render_detail_item( __( 'Apply changes', 'alynt-drime-backups-dashboard' ), ! empty( $clean_capabilities['schedule_management']['apply_supported'] ) ? __( 'Available after a fresh matching preview', 'alynt-drime-backups-dashboard' ) : __( 'Not enabled on the client', 'alynt-drime-backups-dashboard' ) );
			$this->render_detail_item( __( 'Rollback', 'alynt-drime-backups-dashboard' ), __( 'Unavailable in this release; rollback metadata is evidence only and no rollback action exists.', 'alynt-drime-backups-dashboard' ) );
			echo '</dl>';
			$this->render_schedule_preview_form( $site, $schedule );
			$this->render_schedule_apply_form( $site, $schedule, $clean_capabilities, $remote_action_history );
			echo '</section>';
		}

		echo '<p class="description">' . esc_html__( 'Schedule data is redacted capability evidence from the client uploader. Rollback remains unavailable in this release, and Schedule Apply is limited to future Alynt uploader scan cadence changes after a fresh preview.', 'alynt-drime-backups-dashboard' ) . '</p>';
		echo '</div></div>';
	}

	/**
	 * Renders the signed V2.3 schedule-preview form.
	 *
	 * @param array<string,mixed> $site Site row.
	 * @param array<string,mixed> $schedule Schedule summary.
	 * @return void
	 */
	private function render_schedule_preview_form( array $site, array $schedule ) {
		$site_id            = isset( $site['id'] ) ? absint( $site['id'] ) : 0;
		$schedule_id        = isset( $schedule['schedule_id'] ) ? sanitize_key( (string) $schedule['schedule_id'] ) : '';
		$supported_cadences = isset( $schedule['supported_cadences'] ) && is_array( $schedule['supported_cadences'] ) ? $schedule['supported_cadences'] : array();

		if ( 0 === $site_id || '' === $schedule_id || empty( $supported_cadences ) ) {
			echo '<p class="description">' . esc_html__( 'Schedule preview requests become available after this site is opened from its detail screen and the client reports supported cadence choices.', 'alynt-drime-backups-dashboard' ) . '</p>';
			return;
		}

		$description_id = 'adbd-schedule-preview-description-' . $schedule_id;
		?>
		<form method="post" class="adbd-inline-form adbd-schedule-preview-form">
			<?php wp_nonce_field( 'alynt_drime_backups_dashboard_preview_schedule_change' ); ?>
			<input type="hidden" name="alynt_drime_backups_dashboard_action" value="preview_schedule_change">
			<input type="hidden" name="dashboard_site_id" value="<?php echo esc_attr( (string) $site_id ); ?>">
			<input type="hidden" name="schedule_id" value="<?php echo esc_attr( $schedule_id ); ?>">
			<label for="<?php echo esc_attr( $description_id ); ?>-cadence" class="screen-reader-text"><?php esc_html_e( 'Proposed cadence', 'alynt-drime-backups-dashboard' ); ?></label>
			<select id="<?php echo esc_attr( $description_id ); ?>-cadence" name="proposed_cadence" aria-describedby="<?php echo esc_attr( $description_id ); ?>">
				<?php foreach ( $supported_cadences as $cadence ) : ?>
					<option value="<?php echo esc_attr( sanitize_key( (string) $cadence ) ); ?>"><?php echo esc_html( $this->schedule_cadence_label( (string) $cadence ) ); ?></option>
				<?php endforeach; ?>
			</select>
			<button type="submit" class="button" data-busy-label="<?php esc_attr_e( 'Previewing…', 'alynt-drime-backups-dashboard' ); ?>"><?php esc_html_e( 'Preview Schedule Change', 'alynt-drime-backups-dashboard' ); ?></button>
			<span id="<?php echo esc_attr( $description_id ); ?>" class="description"><?php esc_html_e( 'Sends a signed read-only preview request. The client estimates the result and does not apply a schedule change.', 'alynt-drime-backups-dashboard' ); ?></span>
		</form>
		<?php
	}

	/**
	 * Renders the guarded V2.3 schedule-apply form when a fresh preview exists.
	 *
	 * @param array<string,mixed>            $site Site row.
	 * @param array<string,mixed>            $schedule Schedule summary.
	 * @param array<string,mixed>            $capabilities Sanitized capabilities.
	 * @param array<int,array<string,mixed>> $remote_action_history Recent remote action rows.
	 * @return void
	 */
	private function render_schedule_apply_form( array $site, array $schedule, array $capabilities, array $remote_action_history = array() ) {
		$site_id = isset( $site['id'] ) ? absint( $site['id'] ) : 0;

		if (
			0 === $site_id
			|| empty( $capabilities['schedule_management']['apply_supported'] )
			|| ! property_exists( $this, 'remote_actions' )
			|| ! $this->remote_actions instanceof Alynt_Drime_Backups_Dashboard_Remote_Action_Repository
		) {
			return;
		}

		$schedule_id = isset( $schedule['schedule_id'] ) ? sanitize_key( (string) $schedule['schedule_id'] ) : '';
		$preview     = $this->remote_actions->fresh_schedule_preview_for_apply( $site_id, $this->latest_preview_public_id_for_schedule( $site_id, $schedule_id, $remote_action_history ), $capabilities );

		if ( is_wp_error( $preview ) ) {
			echo '<p class="description">' . esc_html__( 'Apply becomes available after a fresh successful preview for this schedule. Run Preview Schedule Change, then Check Now if the preview result has not appeared yet.', 'alynt-drime-backups-dashboard' ) . '</p>';
			return;
		}

		$description_id = 'adbd-schedule-apply-description-' . $schedule_id;
		?>
		<form method="post" class="adbd-inline-form adbd-schedule-apply-form">
			<?php wp_nonce_field( 'alynt_drime_backups_dashboard_apply_schedule_change' ); ?>
			<input type="hidden" name="alynt_drime_backups_dashboard_action" value="apply_schedule_change">
			<input type="hidden" name="dashboard_site_id" value="<?php echo esc_attr( (string) $site_id ); ?>">
			<input type="hidden" name="preview_action_id" value="<?php echo esc_attr( (string) $preview['preview_action_id'] ); ?>">
			<p id="<?php echo esc_attr( $description_id ); ?>" class="description">
				<?php
				echo esc_html(
					sprintf(
						/* translators: 1: current cadence, 2: proposed cadence. */
						__( 'Applies the fresh previewed Alynt uploader scan cadence change from %1$s to %2$s. This changes future scan scheduling for the Alynt uploader only; the upload worker may keep its own cadence and rollback is not available in this release.', 'alynt-drime-backups-dashboard' ),
						$this->schedule_cadence_label( isset( $preview['current_cadence'] ) ? (string) $preview['current_cadence'] : '' ),
						$this->schedule_cadence_label( isset( $preview['proposed_cadence'] ) ? (string) $preview['proposed_cadence'] : '' )
					)
				);
				?>
			</p>
			<label>
				<input type="checkbox" name="schedule_apply_confirm" value="1" aria-describedby="<?php echo esc_attr( $description_id ); ?>">
				<?php esc_html_e( 'I understand this applies only the previewed future Alynt uploader scan cadence change, rollback is unavailable, and this does not create backups or change upload-worker cadence, WPvivid, server-runner, Drime, restore, delete, cleanup, or credentials.', 'alynt-drime-backups-dashboard' ); ?>
			</label>
			<button type="submit" class="button" data-busy-label="<?php esc_attr_e( 'Applying…', 'alynt-drime-backups-dashboard' ); ?>"><?php esc_html_e( 'Apply Previewed Schedule Change', 'alynt-drime-backups-dashboard' ); ?></button>
		</form>
		<?php
	}

	/**
	 * Gets the latest preview public ID for one schedule from recent action history.
	 *
	 * @param int                                 $site_id Site ID.
	 * @param string                              $schedule_id Schedule ID.
	 * @param array<int,array<string,mixed>>|null $remote_action_history Recent remote action rows, or null to fetch.
	 * @return string
	 */
	private function latest_preview_public_id_for_schedule( $site_id, $schedule_id, $remote_action_history = null ) {
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
				is_array( $row )
				&& Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCHEDULE_PREVIEW === ( isset( $row['action_type'] ) ? sanitize_key( (string) $row['action_type'] ) : '' )
				&& 'succeeded' === ( isset( $row['state'] ) ? sanitize_key( (string) $row['state'] ) : '' )
				&& ! empty( $row['public_id'] )
			) {
				return (string) $row['public_id'];
			}
		}

		return '';
	}

	/**
	 * Renders a compact Sites-list schedule capability hint.
	 *
	 * @param array<string,mixed> $payload Latest decoded snapshot payload.
	 * @return string
	 */
	private function schedule_management_row_hint( array $payload ) {
		$capabilities = new Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities();
		$remote       = $this->remote_actions_from_payload( $payload );
		$clean        = $capabilities->sanitize( $remote );
		$clean        = is_wp_error( $clean ) ? array() : $clean;

		if ( ! $capabilities->supports_schedule_management_preview( $clean ) ) {
			return '';
		}

		$schedule_management = $this->schedule_management_summary( $payload );
		$schedule            = ! empty( $schedule_management['schedules'][0] ) && is_array( $schedule_management['schedules'][0] ) ? $schedule_management['schedules'][0] : array();

		if ( empty( $schedule ) ) {
			return '';
		}

		$mode  = ! empty( $clean['schedule_management']['apply_supported'] )
			? __( 'apply gated', 'alynt-drime-backups-dashboard' )
			: __( 'preview only', 'alynt-drime-backups-dashboard' );
		$label = sprintf(
			/* translators: 1: schedule capability mode, 2: schedule label, 3: current cadence. */
			__( 'Schedule: %1$s · %2$s %3$s', 'alynt-drime-backups-dashboard' ),
			$mode,
			$this->schedule_label( $schedule ),
			$this->schedule_cadence_label( isset( $schedule['current_cadence'] ) ? (string) $schedule['current_cadence'] : '' )
		);

		return '<span class="description adbd-row-meta">' . esc_html( $label ) . '</span>';
	}
}
