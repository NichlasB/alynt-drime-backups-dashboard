<?php
/**
 * Status classifier classification helpers.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared classification helpers for status classifier tests.
 */
trait Alynt_Drime_Backups_Dashboard_Status_Classifier_Classification_Fixtures {
	/**
	 * Classifies an active-site snapshot payload at the default fixture time.
	 *
	 * @param array<string,mixed> $payload Payload.
	 * @return array<string,mixed>
	 */
	private function classify_payload( array $payload ) {
		return $this->classify_snapshot( $this->snapshot( $payload ) );
	}

	/**
	 * Classifies an active-site snapshot row at the default fixture time.
	 *
	 * @param array<string,mixed> $snapshot Snapshot row.
	 * @return array<string,mixed>
	 */
	private function classify_snapshot( array $snapshot ) {
		return $this->classify_site_snapshot(
			$this->active_site(),
			$snapshot,
			1700000300
		);
	}

	/**
	 * Classifies a site and snapshot row at a fixture time.
	 *
	 * @param array<string,mixed>      $site Site row.
	 * @param array<string,mixed>|null $snapshot Snapshot row.
	 * @param int                      $now Current timestamp.
	 * @return array<string,mixed>
	 */
	private function classify_site_snapshot( array $site, $snapshot, $now ) {
		return $this->classifier->classify(
			$site,
			$snapshot,
			$now
		);
	}
}
