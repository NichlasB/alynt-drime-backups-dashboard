<?php
/**
 * Diagnostics restore-readiness metrics.
 *
 * @package Alynt_Drime_Backups_Dashboard
 * @since   0.1.58
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds support-safe aggregate metrics from restore-readiness evidence.
 *
 * @since 0.1.58
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Restore_Readiness_Metrics {

	/**
	 * Builds support-safe aggregate restore-readiness diagnostics from one snapshot.
	 *
	 * @since 0.1.53
	 *
	 * @param array<string,mixed>|null $snapshot Snapshot.
	 * @return array<string,int>
	 */
	private function restore_readiness_diagnostics( $snapshot ) {
		$counts = array(
			'reporting_sites'       => 0,
			'evidence_sites'        => 0,
			'incomplete_sites'      => 0,
			'stale_sites'           => 0,
			'incompatible_sites'    => 0,
			'unknown_sites'         => 0,
			'reported_candidates'   => 0,
			'complete_candidates'   => 0,
			'incomplete_candidates' => 0,
			'server_candidates'     => 0,
			'server_complete'       => 0,
			'server_incomplete'     => 0,
			'wpvivid_candidates'    => 0,
			'wpvivid_complete'      => 0,
			'wpvivid_incomplete'    => 0,
		);

		if ( empty( $snapshot ) || ! is_array( $snapshot ) ) {
			return $counts;
		}

		$payload = $this->diagnostic_payload_from_snapshot( $snapshot );

		if ( empty( $payload['restore_readiness'] ) || ! is_array( $payload['restore_readiness'] ) ) {
			return $counts;
		}

		$readiness                 = $payload['restore_readiness'];
		$counts['reporting_sites'] = 1;
		$overall_state             = isset( $readiness['overall_state'] ) ? sanitize_key( $readiness['overall_state'] ) : 'unknown';

		switch ( $overall_state ) {
			case 'evidence_available':
				$counts['evidence_sites'] = 1;
				break;
			case 'incomplete':
				$counts['incomplete_sites'] = 1;
				break;
			case 'stale':
				$counts['stale_sites'] = 1;
				break;
			case 'incompatible':
				$counts['incompatible_sites'] = 1;
				break;
			default:
				$counts['unknown_sites'] = 1;
				break;
		}

		$candidates                    = isset( $readiness['candidates'] ) && is_array( $readiness['candidates'] ) ? $readiness['candidates'] : array();
		$counts['reported_candidates'] = count( $candidates );

		foreach ( $candidates as $candidate ) {
			if ( ! is_array( $candidate ) ) {
				continue;
			}

			$component_state = isset( $candidate['component_state'] ) ? sanitize_key( $candidate['component_state'] ) : 'unknown';
			$checksum_state  = isset( $candidate['checksum_state'] ) ? sanitize_key( $candidate['checksum_state'] ) : 'unknown';
			$manifest_state  = isset( $candidate['manifest_state'] ) ? sanitize_key( $candidate['manifest_state'] ) : 'unknown';
			$sidecar_state   = isset( $candidate['sidecar_state'] ) ? sanitize_key( $candidate['sidecar_state'] ) : 'unknown';
			$source          = isset( $candidate['source'] ) ? sanitize_key( $candidate['source'] ) : '';
			$is_complete     = (
				'complete' === $component_state
				&& 'verified' === $checksum_state
				&& 'compatible' === $manifest_state
				&& 'present' === $sidecar_state
			);

			if ( $is_complete ) {
				++$counts['complete_candidates'];
			} else {
				++$counts['incomplete_candidates'];
			}

			if ( in_array( $source, array( 'server', 'wpvivid' ), true ) ) {
				++$counts[ $source . '_candidates' ];
				++$counts[ $source . ( $is_complete ? '_complete' : '_incomplete' ) ];
			}
		}

		return $counts;
	}
}
