<?php
/**
 * Status classifier site fixture builders.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared site builders for status classifier tests.
 */
trait Alynt_Drime_Backups_Dashboard_Status_Classifier_Site_Fixtures {
	/**
	 * Builds an active site row.
	 *
	 * @param string $last_seen Last seen date.
	 * @return array<string,mixed>
	 */
	private function active_site( $last_seen = '2023-11-14 22:15:00' ) {
		return array(
			'status'       => 'working',
			'paused_at'    => null,
			'last_seen_at' => $last_seen,
		);
	}
}
