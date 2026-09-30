<?php
/**
 * Admin page schedule rollback-preview evidence helper split.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.49
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides display-only schedule rollback-preview evidence helpers.
 *
 * @since 0.1.49
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Schedule_Rollback_Preview_Evidence {
	/**
	 * Gets escaped latest successful rollback-preview evidence for the schedule panel.
	 *
	 * This uses dashboard-local action history only. It does not imply current client
	 * rollback-preview support and it never enables rollback execution.
	 *
	 * @param array<string,mixed>            $schedule Schedule summary.
	 * @param array<int,array<string,mixed>> $remote_action_history Recent remote action rows.
	 * @return string
	 */
	private function latest_schedule_rollback_preview_evidence_markup( array $schedule, array $remote_action_history ) {
		$schedule_id = isset( $schedule['schedule_id'] ) ? sanitize_key( (string) $schedule['schedule_id'] ) : '';

		if ( '' === $schedule_id || empty( $remote_action_history ) ) {
			return '';
		}

		foreach ( $remote_action_history as $row ) {
			if (
				! is_array( $row )
				|| Alynt_Drime_Backups_Dashboard_Remote_Action_Capabilities::ACTION_SCHEDULE_ROLLBACK_PREVIEW !== ( isset( $row['action_type'] ) ? sanitize_key( (string) $row['action_type'] ) : '' )
				|| 'succeeded' !== ( isset( $row['state'] ) ? sanitize_key( (string) $row['state'] ) : '' )
			) {
				continue;
			}

			$context = ! empty( $row['redacted_context_json'] ) ? json_decode( (string) $row['redacted_context_json'], true ) : array();
			$preview = is_array( $context ) && isset( $context['schedule_rollback_preview'] ) && is_array( $context['schedule_rollback_preview'] ) ? $context['schedule_rollback_preview'] : array();

			if ( empty( $preview ) ) {
				continue;
			}

			$preview_schedule_id = isset( $preview['schedule_id'] ) ? sanitize_key( (string) $preview['schedule_id'] ) : '';
			if ( '' !== $preview_schedule_id && $preview_schedule_id !== $schedule_id ) {
				continue;
			}

			return sprintf(
				'<span class="adbd-status-pill is-ready">%1$s</span> %2$s',
				esc_html__( 'Previewed', 'alynt-drime-backups-dashboard' ),
				esc_html( $this->schedule_rollback_preview_evidence_label( $preview ) )
			);
		}

		return '';
	}

	/**
	 * Gets the operator-facing latest rollback-preview evidence label.
	 *
	 * @param array<string,mixed> $preview Rollback-preview context.
	 * @return string
	 */
	private function schedule_rollback_preview_evidence_label( array $preview ) {
		$current  = ! empty( $preview['current_cadence'] ) ? $this->schedule_cadence_label( (string) $preview['current_cadence'] ) : '';
		$rollback = ! empty( $preview['rollback_cadence'] ) ? $this->schedule_cadence_label( (string) $preview['rollback_cadence'] ) : '';

		if ( ! empty( $preview['would_change'] ) ) {
			return sprintf(
				/* translators: %s: cadence transition label. */
				__( 'Latest proof: rollback preview would restore %s. No schedule was changed; rollback execution remains unavailable.', 'alynt-drime-backups-dashboard' ),
				$this->schedule_transition_label(
					$current,
					$rollback,
					__( 'rollback target', 'alynt-drime-backups-dashboard' ),
					__( 'current cadence pending client report', 'alynt-drime-backups-dashboard' ),
					__( 'rollback cadence pending client report', 'alynt-drime-backups-dashboard' )
				)
			);
		}

		if ( '' !== $rollback ) {
			return sprintf(
				/* translators: %s: rollback cadence label. */
				__( 'Latest proof: rollback preview reported the schedule was already at %s. No schedule was changed; rollback execution remains unavailable.', 'alynt-drime-backups-dashboard' ),
				$rollback
			);
		}

		return __( 'Latest proof: rollback preview completed without changing the schedule; rollback execution remains unavailable.', 'alynt-drime-backups-dashboard' );
	}
}
