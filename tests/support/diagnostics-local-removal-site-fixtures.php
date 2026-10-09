<?php
/**
 * Diagnostics local-removal site fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared local-removal readiness site fixture builders.
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Local_Removal_Site_Fixtures {
	/**
	 * Creates an archived revoked local record.
	 *
	 * @param int    $site_id Site ID.
	 * @param string $archived_at Archived timestamp.
	 * @return array<string,mixed>
	 */
	private function archived_revoked_site( $site_id, $archived_at ) {
		return $this->site(
			$site_id,
			array(
				'enrollment_status'         => 'revoked',
				'overall_status'            => 'pending',
				'polling_key_id'            => '',
				'polling_secret_ciphertext' => '',
				'next_poll_at'              => '',
				'archived_at'               => $archived_at,
			)
		);
	}
}
