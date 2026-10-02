<?php
/**
 * Admin page restore-readiness evidence helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.52
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders read-only restore-readiness evidence from the latest client report.
 *
 * @since 0.1.52
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Restore_Readiness_Evidence {
	/**
	 * Renders the restore-readiness evidence panel when reported by the latest client payload.
	 *
	 * @param array<string,mixed>|null $snapshot Latest snapshot row.
	 * @return void
	 */
	private function render_restore_readiness_panel( $snapshot ) {
		$payload   = is_array( $snapshot ) ? $this->decoded_snapshot_payload( $snapshot ) : array();
		$readiness = $this->restore_readiness_from_payload( $payload );

		if ( empty( $readiness ) ) {
			return;
		}

		echo '<div class="adbd-panel adbd-restore-readiness-panel"><h3>' . esc_html__( 'Restore Readiness Evidence', 'alynt-drime-backups-dashboard' ) . '</h3><div class="adbd-panel-body">';
		echo '<p>' . esc_html__( 'This is client-reported evidence only. The dashboard cannot restore this site, download backup packages, stage files, import databases, browse paths, or mutate Drime.', 'alynt-drime-backups-dashboard' ) . '</p>';
		echo '<dl class="adbd-detail-list">';
		$this->render_detail_item( __( 'Overall state', 'alynt-drime-backups-dashboard' ), $this->restore_readiness_state_label( isset( $readiness['overall_state'] ) ? (string) $readiness['overall_state'] : '' ) );
		$this->render_detail_item( __( 'Evidence generated', 'alynt-drime-backups-dashboard' ), isset( $readiness['generated_at'] ) && '' !== $readiness['generated_at'] ? (string) $readiness['generated_at'] : __( 'Not reported', 'alynt-drime-backups-dashboard' ) );
		echo '</dl>';

		if ( empty( $readiness['candidates'] ) || ! is_array( $readiness['candidates'] ) ) {
			echo '<p class="description">' . esc_html__( 'No source-level restore candidates were reported. Treat restore readiness as not verified.', 'alynt-drime-backups-dashboard' ) . '</p></div></div>';
			return;
		}

		echo '<div class="adbd-source-grid">';

		foreach ( $readiness['candidates'] as $candidate ) {
			if ( ! is_array( $candidate ) ) {
				continue;
			}

			$source = isset( $candidate['source'] ) ? (string) $candidate['source'] : '';

			echo '<section class="adbd-source-card is-' . esc_attr( sanitize_key( $source ) ) . '" aria-label="' . esc_attr( $this->restore_readiness_source_label( $source ) ) . '">';
			echo '<h4>' . esc_html( $this->restore_readiness_source_label( $source ) ) . ' <span class="adbd-status-pill is-' . esc_attr( $this->restore_readiness_candidate_tone( $candidate ) ) . '">' . esc_html( $this->restore_readiness_candidate_summary( $candidate ) ) . '</span></h4>';
			echo '<dl class="adbd-detail-list adbd-source-list">';
			$this->render_detail_item( __( 'Latest backup finished', 'alynt-drime-backups-dashboard' ), isset( $candidate['latest_backup_finished_at'] ) && '' !== $candidate['latest_backup_finished_at'] ? (string) $candidate['latest_backup_finished_at'] : __( 'Not reported', 'alynt-drime-backups-dashboard' ) );
			$this->render_detail_item( __( 'Components', 'alynt-drime-backups-dashboard' ), $this->restore_readiness_state_label( isset( $candidate['component_state'] ) ? (string) $candidate['component_state'] : '' ) );
			$this->render_detail_item( __( 'Checksum', 'alynt-drime-backups-dashboard' ), $this->restore_readiness_state_label( isset( $candidate['checksum_state'] ) ? (string) $candidate['checksum_state'] : '' ) );
			$this->render_detail_item( __( 'Manifest', 'alynt-drime-backups-dashboard' ), $this->restore_readiness_state_label( isset( $candidate['manifest_state'] ) ? (string) $candidate['manifest_state'] : '' ) );
			$this->render_detail_item( __( 'Sidecar', 'alynt-drime-backups-dashboard' ), $this->restore_readiness_state_label( isset( $candidate['sidecar_state'] ) ? (string) $candidate['sidecar_state'] : '' ) );
			$this->render_detail_item( __( 'Candidate reference', 'alynt-drime-backups-dashboard' ), isset( $candidate['candidate_ref'] ) && '' !== $candidate['candidate_ref'] ? __( 'Opaque client reference reported', 'alynt-drime-backups-dashboard' ) : __( 'Not reported', 'alynt-drime-backups-dashboard' ) );
			echo '</dl>';
			$this->render_restore_readiness_warnings( $candidate );
			echo '</section>';
		}

		echo '</div>';
		echo '<p class="description">' . esc_html__( 'Missing, unknown, or incomplete restore evidence means “not verified,” not “ready.” Use the manual restore runbook before attempting any restore outside this dashboard.', 'alynt-drime-backups-dashboard' ) . '</p>';
		echo '</div></div>';
	}

	/**
	 * Gets sanitized restore-readiness evidence from a decoded payload.
	 *
	 * @param array<string,mixed> $payload Latest decoded payload.
	 * @return array<string,mixed>
	 */
	private function restore_readiness_from_payload( array $payload ) {
		if ( empty( $payload['restore_readiness'] ) || ! is_array( $payload['restore_readiness'] ) ) {
			return array();
		}

		return $payload['restore_readiness'];
	}

	/**
	 * Renders restore-readiness warnings as support-safe codes.
	 *
	 * @param array<string,mixed> $candidate Candidate evidence.
	 * @return void
	 */
	private function render_restore_readiness_warnings( array $candidate ) {
		if ( empty( $candidate['warnings'] ) || ! is_array( $candidate['warnings'] ) ) {
			return;
		}

		echo '<ul class="adbd-source-warnings">';

		foreach ( $candidate['warnings'] as $warning ) {
			$code = sanitize_key( (string) $warning );

			if ( '' !== $code ) {
				echo '<li><code>' . esc_html( $code ) . '</code></li>';
			}
		}

		echo '</ul>';
	}

	/**
	 * Gets a restore-readiness source label.
	 *
	 * @param string $source Source key.
	 * @return string
	 */
	private function restore_readiness_source_label( $source ) {
		if ( 'server' === $source ) {
			return __( 'Server runner', 'alynt-drime-backups-dashboard' );
		}

		if ( 'wpvivid' === $source ) {
			return __( 'WPvivid', 'alynt-drime-backups-dashboard' );
		}

		return __( 'Unknown source', 'alynt-drime-backups-dashboard' );
	}

	/**
	 * Formats restore-readiness states.
	 *
	 * @param string $state State key.
	 * @return string
	 */
	private function restore_readiness_state_label( $state ) {
		$labels = array(
			'evidence_available' => __( 'Evidence available', 'alynt-drime-backups-dashboard' ),
			'incomplete'         => __( 'Incomplete', 'alynt-drime-backups-dashboard' ),
			'stale'              => __( 'Stale', 'alynt-drime-backups-dashboard' ),
			'incompatible'       => __( 'Incompatible', 'alynt-drime-backups-dashboard' ),
			'complete'           => __( 'Complete', 'alynt-drime-backups-dashboard' ),
			'partial'            => __( 'Partial', 'alynt-drime-backups-dashboard' ),
			'missing'            => __( 'Missing', 'alynt-drime-backups-dashboard' ),
			'verified'           => __( 'Verified', 'alynt-drime-backups-dashboard' ),
			'failed'             => __( 'Failed', 'alynt-drime-backups-dashboard' ),
			'compatible'         => __( 'Compatible', 'alynt-drime-backups-dashboard' ),
			'present'            => __( 'Present', 'alynt-drime-backups-dashboard' ),
			'not_reported'       => __( 'Not reported', 'alynt-drime-backups-dashboard' ),
			'unknown'            => __( 'Unknown', 'alynt-drime-backups-dashboard' ),
		);

		return isset( $labels[ $state ] ) ? $labels[ $state ] : __( 'Unknown', 'alynt-drime-backups-dashboard' );
	}

	/**
	 * Gets candidate badge tone.
	 *
	 * @param array<string,mixed> $candidate Candidate evidence.
	 * @return string
	 */
	private function restore_readiness_candidate_tone( array $candidate ) {
		if (
			'complete' === ( isset( $candidate['component_state'] ) ? $candidate['component_state'] : '' )
			&& 'verified' === ( isset( $candidate['checksum_state'] ) ? $candidate['checksum_state'] : '' )
			&& 'compatible' === ( isset( $candidate['manifest_state'] ) ? $candidate['manifest_state'] : '' )
		) {
			return 'working';
		}

		return 'pending';
	}

	/**
	 * Gets concise candidate summary.
	 *
	 * @param array<string,mixed> $candidate Candidate evidence.
	 * @return string
	 */
	private function restore_readiness_candidate_summary( array $candidate ) {
		if ( 'working' === $this->restore_readiness_candidate_tone( $candidate ) ) {
			return __( 'Evidence available', 'alynt-drime-backups-dashboard' );
		}

		return __( 'Not verified', 'alynt-drime-backups-dashboard' );
	}
}
