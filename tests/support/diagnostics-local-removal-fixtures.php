<?php
/**
 * Diagnostics local-removal readiness fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Shared local-removal readiness fixture builders.
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Local_Removal_Fixtures {
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

	/**
	 * Creates retained action counts keyed by site ID.
	 *
	 * @return array<int,int>
	 */
	private function local_removal_action_counts() {
		return array(
			1 => 3,
			2 => 2,
			3 => 4,
		);
	}

	/**
	 * Creates non-terminal action counts keyed by site ID.
	 *
	 * @return array<int,int>
	 */
	private function local_removal_non_terminal_action_counts() {
		return array(
			2 => 1,
		);
	}

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
