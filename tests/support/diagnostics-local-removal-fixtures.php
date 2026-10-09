<?php
/**
 * Diagnostics local-removal readiness fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/diagnostics-local-removal-site-fixtures.php';
require_once __DIR__ . '/diagnostics-local-removal-action-count-fixtures.php';

/**
 * Shared local-removal readiness fixture builders.
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Local_Removal_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Local_Removal_Site_Fixtures;
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Local_Removal_Action_Count_Fixtures;

	/**
	 * Creates local-removal readiness test sites.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private function local_removal_sites() {
		return array(
			$this->archived_revoked_site( 1, '2026-09-19 18:30:00' ),
			$this->archived_revoked_site( 2, '2026-09-19 18:31:00' ),
			$this->site(
				3,
				array(
					'enrollment_status'             => 'active',
					'overall_status'                => 'working',
					'action_key_id'                 => 'adba_test_key',
					'action_private_key_ciphertext' => 'adbv2.action.ciphertext',
					'archived_at'                   => '2026-09-19 18:32:00',
				)
			),
		);
	}

	/**
	 * Creates local-removal readiness snapshot histories.
	 *
	 * @return array<int,array<int,array<string,mixed>>>
	 */
	private function local_removal_histories() {
		return array(
			1 => array(
				$this->snapshot_row( 'working', '2026-09-18 12:00:00' ),
				$this->snapshot_row( 'pending', '2026-09-18 11:45:00' ),
			),
			2 => array(
				$this->snapshot_row( 'working', '2026-09-18 12:10:00' ),
			),
		);
	}

}
