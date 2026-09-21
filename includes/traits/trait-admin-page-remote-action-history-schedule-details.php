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
 * Provides remote action history schedule-detail label helpers.
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Remote_Action_History_Schedule_Details {

	/**
	 * Gets a compact schedule-management summary for a history row.
	 *
	 * @param array<string,mixed> $row History row.
	 * @return string
	 */
	private function remote_action_schedule_details_label( array $row ) {
		$context = ! empty( $row['redacted_context_json'] ) ? json_decode( (string) $row['redacted_context_json'], true ) : array();

		if ( ! is_array( $context ) ) {
			return '';
		}

		$preview          = isset( $context['schedule_preview'] ) && is_array( $context['schedule_preview'] ) ? $context['schedule_preview'] : array();
		$apply            = isset( $context['schedule_apply'] ) && is_array( $context['schedule_apply'] ) ? $context['schedule_apply'] : array();
		$rollback_preview = isset( $context['schedule_rollback_preview'] ) && is_array( $context['schedule_rollback_preview'] ) ? $context['schedule_rollback_preview'] : array();

		if ( ! empty( $apply['previous_cadence'] ) || ! empty( $apply['applied_cadence'] ) ) {
			$previous = ! empty( $apply['previous_cadence'] ) ? $this->schedule_cadence_label( (string) $apply['previous_cadence'] ) : '';
			$applied  = ! empty( $apply['applied_cadence'] ) ? $this->schedule_cadence_label( (string) $apply['applied_cadence'] ) : '';
			$detail   = $this->schedule_transition_label(
				$previous,
				$applied,
				__( 'Applied cadence', 'alynt-drime-backups-dashboard' ),
				__( 'previous cadence pending client report', 'alynt-drime-backups-dashboard' ),
				__( 'applied cadence pending client report', 'alynt-drime-backups-dashboard' )
			);

			if ( ! empty( $apply['new_next_run_at'] ) ) {
				$detail .= '; ' . sprintf(
					/* translators: %s: next run date/time. */
					__( 'Next run %s', 'alynt-drime-backups-dashboard' ),
					$this->datetime_label( (string) $apply['new_next_run_at'] )
				);
			}

			$detail .= '; ' . __( 'Alynt uploader scan cadence only; upload worker cadence may remain separate', 'alynt-drime-backups-dashboard' );

			$rollback_label = isset( $apply['rollback_metadata'] ) && is_array( $apply['rollback_metadata'] ) ? $this->remote_action_rollback_metadata_label( $apply['rollback_metadata'] ) : '';
			if ( '' !== $rollback_label ) {
				$detail .= '; ' . $rollback_label;
			}

			return $detail;
		}

		if ( ! empty( $rollback_preview['current_cadence'] ) || ! empty( $rollback_preview['rollback_cadence'] ) ) {
			$parts = array(
				$this->rollback_preview_result_label( $rollback_preview ),
			);

			if ( ! empty( $rollback_preview['current_next_run_at'] ) ) {
				$parts[] = sprintf(
					/* translators: %s: current next run date/time. */
					__( 'Current next run %s', 'alynt-drime-backups-dashboard' ),
					$this->datetime_label( (string) $rollback_preview['current_next_run_at'] )
				);
			}

			if ( ! empty( $rollback_preview['rollback_next_run_estimate_at'] ) ) {
				$parts[] = sprintf(
					/* translators: %s: next run date/time. */
					__( 'Rollback estimate next run %s', 'alynt-drime-backups-dashboard' ),
					$this->datetime_label( (string) $rollback_preview['rollback_next_run_estimate_at'] )
				);
			}

			$parts[] = ! empty( $rollback_preview['would_change'] )
				? __( 'If applied in a future release, rollback would change the Alynt scan cadence; this preview did not change it', 'alynt-drime-backups-dashboard' )
				: __( 'No cadence change would be needed based on this preview; this preview did not change it', 'alynt-drime-backups-dashboard' );
			$parts[] = __( 'Rollback execution remains unavailable', 'alynt-drime-backups-dashboard' );

			return implode( '; ', array_filter( $parts ) );
		}

		if ( ! empty( $preview['current_cadence'] ) || ! empty( $preview['proposed_cadence'] ) ) {
			$current  = ! empty( $preview['current_cadence'] ) ? $this->schedule_cadence_label( (string) $preview['current_cadence'] ) : '';
			$proposed = ! empty( $preview['proposed_cadence'] ) ? $this->schedule_cadence_label( (string) $preview['proposed_cadence'] ) : '';

			return $this->schedule_transition_label(
				$current,
				$proposed,
				__( 'Preview target', 'alynt-drime-backups-dashboard' ),
				__( 'current cadence pending client report', 'alynt-drime-backups-dashboard' ),
				__( 'proposed cadence pending client report', 'alynt-drime-backups-dashboard' )
			);
		}

		return '';
	}

	/**
	 * Gets the compact default schedule-management summary for a history row.
	 *
	 * @param array<string,mixed> $row History row.
	 * @return string
	 */
	private function remote_action_schedule_summary_label( array $row ) {
		$context = ! empty( $row['redacted_context_json'] ) ? json_decode( (string) $row['redacted_context_json'], true ) : array();

		if ( ! is_array( $context ) ) {
			return '';
		}

		$preview          = isset( $context['schedule_preview'] ) && is_array( $context['schedule_preview'] ) ? $context['schedule_preview'] : array();
		$apply            = isset( $context['schedule_apply'] ) && is_array( $context['schedule_apply'] ) ? $context['schedule_apply'] : array();
		$rollback_preview = isset( $context['schedule_rollback_preview'] ) && is_array( $context['schedule_rollback_preview'] ) ? $context['schedule_rollback_preview'] : array();

		if ( ! empty( $apply['previous_cadence'] ) || ! empty( $apply['applied_cadence'] ) ) {
			$previous = ! empty( $apply['previous_cadence'] ) ? $this->schedule_cadence_label( (string) $apply['previous_cadence'] ) : '';
			$applied  = ! empty( $apply['applied_cadence'] ) ? $this->schedule_cadence_label( (string) $apply['applied_cadence'] ) : '';

			return $this->schedule_transition_label(
				$previous,
				$applied,
				__( 'Applied cadence', 'alynt-drime-backups-dashboard' ),
				__( 'previous cadence pending client report', 'alynt-drime-backups-dashboard' ),
				__( 'applied cadence pending client report', 'alynt-drime-backups-dashboard' )
			);
		}

		if ( ! empty( $rollback_preview['current_cadence'] ) || ! empty( $rollback_preview['rollback_cadence'] ) ) {
			return $this->rollback_preview_result_label( $rollback_preview );
		}

		if ( ! empty( $preview['current_cadence'] ) || ! empty( $preview['proposed_cadence'] ) ) {
			$current  = ! empty( $preview['current_cadence'] ) ? $this->schedule_cadence_label( (string) $preview['current_cadence'] ) : '';
			$proposed = ! empty( $preview['proposed_cadence'] ) ? $this->schedule_cadence_label( (string) $preview['proposed_cadence'] ) : '';

			return $this->schedule_transition_label(
				$current,
				$proposed,
				__( 'Preview target', 'alynt-drime-backups-dashboard' ),
				__( 'current cadence pending client report', 'alynt-drime-backups-dashboard' ),
				__( 'proposed cadence pending client report', 'alynt-drime-backups-dashboard' )
			);
		}

		return '';
	}

	/**
	 * Gets an operator-facing rollback-preview result label.
	 *
	 * @param array<string,mixed> $rollback_preview Rollback-preview context.
	 * @return string
	 */
	private function rollback_preview_result_label( array $rollback_preview ) {
		$current  = ! empty( $rollback_preview['current_cadence'] ) ? $this->schedule_cadence_label( (string) $rollback_preview['current_cadence'] ) : '';
		$rollback = ! empty( $rollback_preview['rollback_cadence'] ) ? $this->schedule_cadence_label( (string) $rollback_preview['rollback_cadence'] ) : '';

		if ( ! empty( $rollback_preview['would_change'] ) ) {
			return sprintf(
				/* translators: %s: cadence transition label. */
				__( 'Rollback preview would restore: %s', 'alynt-drime-backups-dashboard' ),
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
				__( 'Rollback preview: already at %s; no change needed', 'alynt-drime-backups-dashboard' ),
				$rollback
			);
		}

		return $this->schedule_transition_label(
			$current,
			$rollback,
			__( 'Rollback preview', 'alynt-drime-backups-dashboard' ),
			__( 'current cadence pending client report', 'alynt-drime-backups-dashboard' ),
			__( 'rollback cadence pending client report', 'alynt-drime-backups-dashboard' )
		);
	}

	/**
	 * Gets a compact rollback-readiness label for a schedule apply row.
	 *
	 * @param array<string,mixed> $metadata Rollback metadata.
	 * @return string
	 */
	private function remote_action_rollback_metadata_label( array $metadata ) {
		if ( empty( $metadata['captured'] ) ) {
			return '';
		}

		$parts  = array(
			__( 'Rollback metadata captured as evidence only; rollback action unavailable', 'alynt-drime-backups-dashboard' ),
		);
		$reason = isset( $metadata['reason'] ) ? sanitize_key( (string) $metadata['reason'] ) : '';

		if ( '' !== $reason ) {
			$parts[] = sprintf(
				/* translators: %s: rollback unavailable reason code. */
				__( 'Reason %s', 'alynt-drime-backups-dashboard' ),
				$reason
			);
		}

		if ( ! empty( $metadata['expires_at'] ) ) {
			$parts[] = sprintf(
				/* translators: %s: rollback metadata expiry date/time. */
				__( 'metadata expires %s', 'alynt-drime-backups-dashboard' ),
				$this->datetime_label( (string) $metadata['expires_at'] )
			);
		}

		return implode( '; ', $parts );
	}
}
