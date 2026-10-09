<?php
/**
 * Diagnostics restore-readiness aggregate fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/diagnostics-restore-readiness-candidate-fixtures.php';

/**
 * Shared restore-readiness aggregate fixture builders.
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Restore_Readiness_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Restore_Readiness_Candidate_Fixtures;

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
}
