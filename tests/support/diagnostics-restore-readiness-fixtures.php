<?php
/**
 * Diagnostics restore-readiness aggregate fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/diagnostics-restore-readiness-candidate-fixtures.php';
require_once __DIR__ . '/diagnostics-restore-readiness-snapshot-fixtures.php';

/**
 * Shared restore-readiness aggregate fixture builders.
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Restore_Readiness_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Restore_Readiness_Candidate_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Restore_Readiness_Snapshot_Fixtures;

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

}
