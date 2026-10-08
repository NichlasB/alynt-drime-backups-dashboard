<?php
/**
 * Test support for diagnostics tests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

require_once __DIR__ . '/diagnostics-core-fixtures.php';
require_once __DIR__ . '/diagnostics-support-summary-test-harness.php';

/**
 * Shared fixture builders for diagnostics tests.
 */
trait Alynt_Drime_Backups_Dashboard_Diagnostics_Test_Fixtures {
	use Alynt_Drime_Backups_Dashboard_Diagnostics_Core_Fixtures;

	/**
	 * Collects diagnostics for focused site/snapshot fixtures.
	 *
	 * @param array<int,array<string,mixed>> $sites Sites.
	 * @param array<int,array<string,mixed>> $snapshots Latest snapshots keyed by site ID.
	 * @param array<int,array<int,array<string,mixed>>> $histories Recent snapshot histories keyed by site ID.
	 * @param array<int,int> $action_counts Retained action counts keyed by site ID.
	 * @param array<int,int> $non_terminal_action_counts Non-terminal action counts keyed by site ID.
	 * @return array<string,mixed>
	 */
	private function collect_diagnostics( array $sites, array $snapshots = array(), array $histories = array(), array $action_counts = array(), array $non_terminal_action_counts = array() ) {
		$diagnostics = new Alynt_Drime_Backups_Dashboard_Diagnostics(
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Site_Repository( $sites ),
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Snapshot_Repository( $snapshots, $histories ),
			new Alynt_Drime_Backups_Dashboard_Status_Classifier(),
			null,
			new Alynt_Drime_Backups_Dashboard_Test_Diagnostics_Remote_Action_Repository( $action_counts, $non_terminal_action_counts )
		);

		return $diagnostics->collect();
	}
}
