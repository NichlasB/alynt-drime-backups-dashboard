<?php
/**
 * Diagnostics schedule-management aggregate fixtures.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/diagnostics-schedule-management-payload-fixtures.php';

/**
 * Shared schedule-management aggregate fixture builders.
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Schedule_Management_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Schedule_Management_Payload_Fixtures;

	/**
	 * Creates schedule-management aggregate test sites.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private function schedule_management_sites() {
		return array(
			$this->site( 1 ),
			$this->site( 2 ),
			$this->site( 3 ),
		);
	}

	/**
	 * Creates schedule-management aggregate test snapshots.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private function schedule_management_snapshots() {
		return array(
			1 => $this->snapshot( $this->schedule_management_payload( true, false, false, 'every_15_minutes' ) ),
			2 => $this->snapshot(),
			3 => $this->snapshot( $this->schedule_management_payload( false, true, true, 'every_30_minutes' ) ),
		);
	}
}
