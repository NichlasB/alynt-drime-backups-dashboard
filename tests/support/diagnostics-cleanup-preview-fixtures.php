<?php
/**
 * Diagnostics cleanup-preview aggregate fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/diagnostics-cleanup-preview-payload-fixtures.php';

/**
 * Shared cleanup-preview aggregate fixture builders.
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Cleanup_Preview_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Cleanup_Preview_Payload_Fixtures;

	/**
	 * Creates cleanup-preview aggregate test sites.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private function cleanup_preview_sites() {
		return array(
			$this->site( 1 ),
			$this->site( 2 ),
		);
	}

	/**
	 * Creates cleanup-preview aggregate test snapshots.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private function cleanup_preview_snapshots() {
		return array(
			1 => $this->snapshot( $this->cleanup_preview_payload( true, false, false, true ) ),
			2 => $this->snapshot( $this->cleanup_preview_payload( true, true, true, false ) ),
		);
	}
}
