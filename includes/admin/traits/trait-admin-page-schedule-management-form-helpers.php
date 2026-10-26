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

	use Alynt_Drime_Backups_Dashboard_Admin_Page_Schedule_Management_Action_Forms;
	use Alynt_Drime_Backups_Dashboard_Admin_Page_Schedule_Rollback_Preview_Helpers;

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
			$this->render_detail_item( __( 'Rollback', 'alynt-drime-backups-dashboard' ), __( 'Execution unavailable; rollback apply is not available in this release.', 'alynt-drime-backups-dashboard' ) );
			$this->render_detail_item( __( 'Rollback preview', 'alynt-drime-backups-dashboard' ), $this->schedule_rollback_preview_readiness_markup( $site, $schedule, $clean_capabilities, $remote_action_history ), true );
			$latest_rollback_preview = $this->latest_schedule_rollback_preview_evidence_markup( $schedule, $remote_action_history );
			if ( '' !== $latest_rollback_preview ) {
				$this->render_detail_item( __( 'Latest rollback preview', 'alynt-drime-backups-dashboard' ), $latest_rollback_preview, true );
			}
			echo '</dl>';
			$this->render_schedule_preview_form( $site, $schedule );
			$this->render_schedule_apply_form( $site, $schedule, $clean_capabilities, $remote_action_history );
			$this->render_schedule_rollback_preview_form( $site, $schedule, $clean_capabilities, $remote_action_history );
			echo '</section>';
		}

		echo '<p class="description">' . esc_html__( 'Schedule data is redacted capability evidence from the client uploader. Rollback execution remains unavailable in this release; rollback preview is non-mutating and only asks the client what a rollback would look like after a successful apply with complete rollback metadata.', 'alynt-drime-backups-dashboard' ) . '</p>';
		echo '</div></div>';
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
