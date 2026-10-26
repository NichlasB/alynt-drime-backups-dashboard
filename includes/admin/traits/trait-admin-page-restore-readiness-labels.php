<?php
/**
 * Admin page restore-readiness label helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.59
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Formats restore-readiness evidence labels and tones.
 *
 * @since 0.1.59
 */
trait Alynt_Drime_Backups_Dashboard_Admin_Page_Restore_Readiness_Labels {
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
			'not_reported'       => __( 'Not reported', 'alynt-drime-backups-dashboard' ),
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
			'unknown'            => __( 'Unknown', 'alynt-drime-backups-dashboard' ),
		);

		return isset( $labels[ $state ] ) ? $labels[ $state ] : __( 'Unknown', 'alynt-drime-backups-dashboard' );
	}

	/**
	 * Gets overall row-hint tone for restore-readiness evidence.
	 *
	 * @param string $state Overall state.
	 * @return string
	 */
	private function restore_readiness_overall_tone( $state ) {
		if ( 'evidence_available' === $state ) {
			return 'ok';
		}

		if ( in_array( $state, array( 'incomplete', 'stale', 'incompatible' ), true ) ) {
			return 'warning';
		}

		return 'unknown';
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
