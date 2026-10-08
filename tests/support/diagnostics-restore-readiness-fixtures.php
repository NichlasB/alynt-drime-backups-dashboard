<?php
/**
 * Diagnostics restore-readiness aggregate fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared restore-readiness aggregate fixture builders.
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Restore_Readiness_Fixtures {
	/**
	 * Creates restore-readiness aggregate test sites.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private function restore_readiness_sites() {
		return array(
			$this->site( 1 ),
			$this->site( 2 ),
			$this->site( 3 ),
		);
	}

	/**
	 * Creates restore-readiness aggregate test snapshots.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private function restore_readiness_snapshots() {
		return array(
			1 => $this->snapshot(
				array(
					'restore_readiness' => array(
						'overall_state' => 'evidence_available',
						'candidates'    => array(
							$this->restore_readiness_candidate(
								'server',
								'opaque-do-not-export',
								'complete',
								'verified',
								'compatible',
								'present'
							),
							$this->restore_readiness_candidate(
								'wpvivid',
								'opaque-do-not-export-2',
								'unknown',
								'not_reported',
								'not_reported',
								'not_reported'
							),
						),
					),
				)
			),
			2 => $this->snapshot(),
			3 => $this->snapshot(
				array(
					'restore_readiness' => array(
						'overall_state' => 'incomplete',
						'candidates'    => array(
							$this->restore_readiness_candidate(
								'server',
								'another-opaque-ref',
								'partial',
								'unknown',
								'unknown',
								'missing'
							),
						),
					),
				)
			),
		);
	}

	/**
	 * Creates one restore-readiness candidate fixture.
	 *
	 * @param string $source Source slug.
	 * @param string $candidate_ref Opaque candidate reference.
	 * @param string $component_state Component state.
	 * @param string $checksum_state Checksum state.
	 * @param string $manifest_state Manifest state.
	 * @param string $sidecar_state Sidecar state.
	 * @return array<string,string>
	 */
	private function restore_readiness_candidate( $source, $candidate_ref, $component_state, $checksum_state, $manifest_state, $sidecar_state ) {
		return array(
			'source'          => $source,
			'candidate_ref'   => $candidate_ref,
			'component_state' => $component_state,
			'checksum_state'  => $checksum_state,
			'manifest_state'  => $manifest_state,
			'sidecar_state'   => $sidecar_state,
		);
	}
}
