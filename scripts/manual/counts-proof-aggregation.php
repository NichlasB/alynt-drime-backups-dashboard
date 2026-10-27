<?php
/**
 * Actual private Diagnostics count helper over real fixture reads, not page auth.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Compares the actual metrics with the previous scalar strategy and an oracle.
 *
 * @return void
 */
function adb_counts_aggregate_cases() {
	global $queries, $groups;
	$sites = array();
	foreach ( range( 1, 52 ) as $id ) {
		$sites[] = array(
			'id'                        => $id,
			'enrollment_status'         => 'revoked',
			'archived_at'               => 3 === $id ? '' : '2026-10-01 00:00:00',
			'polling_key_id'            => '',
			'polling_secret_ciphertext' => '',
			'next_poll_at'              => '',
			'paused_at'                 => '',
		);
	}
	$method = new ReflectionMethod( Alynt_Drime_Backups_Dashboard_Diagnostics::class, 'count_diagnostics' );
	$before = $queries;
	$actual = $method->invoke( new Alynt_Drime_Backups_Dashboard_Diagnostics(), $sites, array(), time() )['local_removal'];
	adb_counts_check( 6 === $queries - $before, 'actual archived metrics use six independent grouped statements' );
	adb_counts_check(
		array(
			'archived_records'         => 51,
			'ready_records'            => 26,
			'blocked_records'          => 25,
			'retained_snapshot_rows'   => 183,
			'retained_action_rows'     => 325,
			'non_terminal_action_rows' => 75,
		) === $actual,
		'positive analytic readiness/count oracle, visible exclusion and empty site'
	);
	$snapshots = new class() extends Alynt_Drime_Backups_Dashboard_Snapshot_Repository {
		/**
		 * Reproduces the previous snapshot-count strategy over the physical fixture.
		 *
		 * @param array<int> $ids Site IDs.
		 * @return array<int,int>
		 */
		public function counts_by_site_ids( array $ids ) {
			$counts = array();
			foreach ( $ids as $id ) {
				$counts[ $id ] = max( 0, $this->count_for_site( $id ) ); }
			return $counts;
		}
	};
	$actions   = new class() extends Alynt_Drime_Backups_Dashboard_Remote_Action_Repository {
		/**
		 * Reproduces the independent previous action-count strategies.
		 *
		 * @param array<int> $ids Site IDs.
		 * @param bool       $non_terminal Select non-terminal counts.
		 * @return array<int,int>
		 */
		public function counts_by_site_ids( array $ids, $non_terminal = false ) {
			$counts = array();
			foreach ( array_filter( array_map( 'absint', $ids ) ) as $id ) {
				$counts[ $id ] = max( 0, $non_terminal ? $this->count_non_terminal_for_site( $id ) : $this->count_for_site( $id ) ); }
			return $counts;
		}
	};
	$before    = $queries;
	$reference = $method->invoke( new Alynt_Drime_Backups_Dashboard_Diagnostics( null, $snapshots, null, null, $actions ), $sites, array(), time() )['local_removal'];
	adb_counts_check( 153 === $queries - $before && $reference === $actual, 'actual six-query aggregate equals previous153 scalar reads' );
	$sites     = array(
		array(
			'id'                => -2,
			'enrollment_status' => 'revoked',
			'archived_at'       => '2026-10-01 00:00:00',
		),
		array(
			'id'                => 0,
			'enrollment_status' => 'revoked',
			'archived_at'       => '2026-10-01 00:00:00',
		),
	);
	$actual    = $method->invoke( new Alynt_Drime_Backups_Dashboard_Diagnostics(), $sites, array(), time() )['local_removal'];
	$reference = $method->invoke( new Alynt_Drime_Backups_Dashboard_Diagnostics( null, $snapshots, null, null, $actions ), $sites, array(), time() )['local_removal'];
	adb_counts_check(
		$actual === $reference && array(
			'archived_records'         => 2,
			'ready_records'            => 2,
			'blocked_records'          => 0,
			'retained_snapshot_rows'   => 2,
			'retained_action_rows'     => 5,
			'non_terminal_action_rows' => 0,
		) === $actual,
		'signed/zero original scalar semantics preserved in actual aggregate'
	);
	$groups[] = 'actual private Diagnostics aggregation, positive ready/blocked oracle, visible/empty exclusions and scalar-strategy equality';
}
