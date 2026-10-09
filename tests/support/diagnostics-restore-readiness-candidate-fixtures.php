<?php
/**
 * Diagnostics restore-readiness candidate fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared restore-readiness candidate fixture builders.
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Restore_Readiness_Candidate_Fixtures {
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
