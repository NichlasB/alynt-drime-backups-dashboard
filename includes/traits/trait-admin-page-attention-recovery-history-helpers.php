<?php
/**
 * Admin page attention/recovery history helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides attention/recovery transition rendering helpers for dashboard admin screens.
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Attention_Recovery_History_Helpers {

	/**
	 * Renders a compact status transition panel for recent attention/recovery context.
	 *
	 * @param array<int,array<string,mixed>> $history Snapshot history, newest first.
	 * @return void
	 */
	private function render_attention_recovery_history( array $history ) {
		$transitions = $this->attention_recovery_transitions( $history );

		echo '<div class="adbd-panel"><h3>' . esc_html__( 'Attention / Recovery History', 'alynt-drime-backups-dashboard' ) . '</h3>';

		if ( empty( $transitions ) ) {
			echo '<div class="adbd-panel-body"><p>' . esc_html__( 'No recent attention or recovery transitions are available in the retained snapshot window.', 'alynt-drime-backups-dashboard' ) . '</p></div></div>';
			return;
		}

		echo '<div class="adbd-table-wrap"><table class="widefat striped adbd-history-table"><caption>' . esc_html__( 'Recent status changes derived from retained redacted snapshots for this site', 'alynt-drime-backups-dashboard' ) . '</caption><thead><tr><th scope="col">' . esc_html__( 'Observed', 'alynt-drime-backups-dashboard' ) . '</th><th scope="col">' . esc_html__( 'Change', 'alynt-drime-backups-dashboard' ) . '</th><th scope="col">' . esc_html__( 'Evidence', 'alynt-drime-backups-dashboard' ) . '</th></tr></thead><tbody>';

		foreach ( $transitions as $transition ) {
			$from_label = $this->status_history_category_label( $transition['from'] );
			$to_label   = $this->status_history_category_label( $transition['to'] );

			echo '<tr><td>' . $this->time_html( $transition['observed_at'] ) . '</td><td>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- time_html() returns escaped markup.

			if ( 'recovered' === $transition['type'] ) {
				printf(
					/* translators: 1: previous status, 2: current status. */
					esc_html__( 'Recovered from %1$s to %2$s.', 'alynt-drime-backups-dashboard' ),
					esc_html( $from_label ),
					esc_html( $to_label )
				);
			} elseif ( 'entered_attention' === $transition['type'] ) {
				printf(
					/* translators: 1: current status, 2: previous status. */
					esc_html__( 'Entered %1$s from %2$s.', 'alynt-drime-backups-dashboard' ),
					esc_html( $to_label ),
					esc_html( $from_label )
				);
			} else {
				printf(
					/* translators: 1: previous status, 2: current status. */
					esc_html__( 'Changed from %1$s to %2$s.', 'alynt-drime-backups-dashboard' ),
					esc_html( $from_label ),
					esc_html( $to_label )
				);
			}

			echo '</td><td>' . esc_html( $this->snapshot_history_evidence_text( $transition['row'] ) ) . '</td></tr>';
		}

		echo '</tbody></table></div></div>';
	}

	/**
	 * Gets bounded status transitions from recent snapshot rows.
	 *
	 * @param array<int,array<string,mixed>> $history Snapshot history, newest first.
	 * @param int                            $limit Maximum transitions.
	 * @return array<int,array<string,mixed>>
	 */
	private function attention_recovery_transitions( array $history, $limit = 5 ) {
		$limit       = max( 1, min( 20, (int) $limit ) );
		$chronology  = array_reverse( $history );
		$previous    = null;
		$transitions = array();

		foreach ( $chronology as $row ) {
			$current = isset( $row['overall_status'] ) ? sanitize_key( $row['overall_status'] ) : '';

			if ( '' === $current ) {
				continue;
			}

			if ( null !== $previous && $previous['category'] !== $current ) {
				$transitions[] = array(
					'from'        => $previous['category'],
					'to'          => $current,
					'type'        => $this->attention_recovery_transition_type( $previous['category'], $current ),
					'observed_at' => isset( $row['observed_at'] ) ? (string) $row['observed_at'] : '',
					'row'         => $row,
				);
			}

			$previous = array(
				'category' => $current,
			);
		}

		return array_reverse( array_slice( $transitions, -1 * $limit ) );
	}

	/**
	 * Classifies a status transition for operator wording.
	 *
	 * @param string $from Previous status.
	 * @param string $to Current status.
	 * @return string
	 */
	private function attention_recovery_transition_type( $from, $to ) {
		$attention_categories = array(
			'incompatible',
			'needs_attention',
			'not_configured',
			'not_reporting',
		);
		$from_attention       = in_array( $from, $attention_categories, true );
		$to_attention         = in_array( $to, $attention_categories, true );

		if ( $from_attention && 'working' === $to ) {
			return 'recovered';
		}

		if ( $to_attention ) {
			return 'entered_attention';
		}

		return 'changed';
	}
}
