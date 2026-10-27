<?php
/**
 * Finite owned fixture setup and real grouped/scalar count cases.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared -- Exact approved owned fixture statements only.
/**
 * Asserts fixed non-secret labels, never raw SQL or private row contents.
 *
 * @param bool   $condition Result.
 * @param string $label Fixed label.
 * @return void
 * @throws RuntimeException For failed proof assertions.
 */
function adb_counts_check( $condition, $label ) {
	++$GLOBALS['checks'];
	if ( ! $condition ) {
		throw new RuntimeException( 'Owned count assertion: ' . $label ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Private fixed CLI label.
	}
}

/**
 * Gets the independent analytic fixture oracle, including existing ID policies.
 *
 * @param string $kind Count kind.
 * @param int    $site Site ID.
 * @return int
 */
function adb_counts_expected( $kind, $site ) {
	$site = 'snapshots' === $kind ? (int) $site : absint( $site );
	if ( 'snapshots' === $kind ) {
		return 0 === $site ? 2 : ( 1 === $site ? 60 : ( $site >= 2 && $site <= 51 ? 1 + $site % 4 : ( 999 === $site ? 1 : 0 ) ) );
	}
	if ( $site >= 1 && $site <= 51 ) {
		return 'actions' === $kind ? ( $site % 2 ? 8 : 5 ) : ( $site % 2 ? 3 : 0 );
	}
	return 999 === $site ? 1 : 0;
}

/**
 * Calls the actual repository, never a simulated database count.
 *
 * @param string     $kind Count kind.
 * @param array<int> $ids Site IDs.
 * @return array<int,int>
 */
function adb_counts_read( $kind, array $ids ) {
	return 'snapshots' === $kind ? ( new Alynt_Drime_Backups_Dashboard_Snapshot_Repository() )->counts_by_site_ids( $ids ) : ( new Alynt_Drime_Backups_Dashboard_Remote_Action_Repository() )->counts_by_site_ids( $ids, 'non_terminal' === $kind );
}

/**
 * Seeds only bounded synthetic rows, with no real sites/users/action execution.
 *
 * @return void
 */
function adb_counts_seed() {
	global $wpdb, $tables, $groups;
	foreach ( array_merge( array( 0 ), range( 1, 51 ), array( 999 ) ) as $site ) {
		$count = adb_counts_expected( 'snapshots', $site );
		foreach ( range( 1, $count ) as $offset ) {
			adb_counts_check(
				1 === $wpdb->insert(
					$tables['snapshots'],
					array(
						'id'                => $site * 1000 + $offset,
						'dashboard_site_id' => $site,
						'observed_at'       => '2026-10-01 00:00:00',
						'payload_json'      => '{"owned":"π count marker"}',
					)
				) && '' === $wpdb->last_error,
				'owned snapshot inserted'
			);
		}
		$states = array( 'completed', 'failed', 'rejected', 'cancelled', 'stale' );
		if ( $site % 2 || 0 === $site ) {
			$states = array_merge( $states, array( 'queued_for_dispatch', 'accepted', 'running' ) );
		}
		if ( 0 === $site || 999 === $site ) {
			$states = array( 'queued_for_dispatch' );
		}
		foreach ( $states as $index => $state ) {
			$id = $site * 1000 + $index + 1;
			adb_counts_check(
				1 === $wpdb->insert(
					$tables['actions'],
					array(
						'id'                    => $id,
						'public_id'             => sprintf( '00000000-0000-4000-8000-%012d', $id ),
						'dashboard_site_id'     => $site,
						'action_type'           => 'owned_count_fixture',
						'state'                 => $state,
						'client_state'          => 'running',
						'requested_at'          => '2026-10-01 00:00:00',
						'expires_at'            => '2099-01-01 00:00:00',
						'created_at'            => '2026-10-01 00:00:00',
						'updated_at'            => '2026-10-01 00:00:00',
						'redacted_context_json' => '{}',
					)
				) && '' === $wpdb->last_error,
				'owned action DATA inserted, never dispatched'
			);
		}
	}
	adb_counts_check( '190' === (string) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $tables['snapshots'] ) && '' === $wpdb->last_error, 'exact snapshot fixture size' );
	adb_counts_check( '335' === (string) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $tables['actions'] ) && '' === $wpdb->last_error, 'exact action fixture size' );
	$groups[] = '190 snapshot and335 action rows, three non-terminal states, terminal/client-state counterexamples and empty/foreign/zero IDs';
}

/**
 * Qualifies real grouped counts against original scalar readers and exact data.
 *
 * @return void
 */
function adb_counts_read_cases() {
	global $wpdb, $queries, $groups;
	$snapshots = new Alynt_Drime_Backups_Dashboard_Snapshot_Repository();
	$actions   = new Alynt_Drime_Backups_Dashboard_Remote_Action_Repository();
	foreach ( array( 'snapshots', 'actions', 'non_terminal' ) as $kind ) {
		$before = $queries;
		adb_counts_check( array() === adb_counts_read( $kind, array() ) && $before === $queries, 'empty request does not query' );
		$actual = adb_counts_read( $kind, range( 1, 51 ) );
		adb_counts_check( '' === $wpdb->last_error && 2 === $queries - $before && 51 === count( $actual ), '51-site kind uses exactly two grouped statements' );
		foreach ( range( 1, 51 ) as $site ) {
			$scalar = 'snapshots' === $kind ? $snapshots->count_for_site( $site ) : ( 'actions' === $kind ? $actions->count_for_site( $site ) : $actions->count_non_terminal_for_site( $site ) );
			adb_counts_check( '' === $wpdb->last_error && adb_counts_expected( $kind, $site ) === $actual[ $site ] && $scalar === $actual[ $site ], 'grouped, scalar and analytic counts agree per site' );
		}
		foreach ( array(
			100 => 2,
			500 => 10,
		) as $size => $budget ) {
			$before = $queries;
			$actual = adb_counts_read( $kind, range( 1, $size ) );
			adb_counts_check( '' === $wpdb->last_error && $budget === $queries - $before && count( $actual ) === $size && 0 === $actual[ $size ] && ! isset( $actual[999] ), 'larger batch budget, empty keys and foreign exclusion' );
		}
	}
	adb_counts_check(
		array(
			-2 => 0,
			0  => 2,
			1  => 60,
		) === adb_counts_read( 'snapshots', array( -2, 0, 1, '1' ) ),
		'snapshot signed/zero IDs and count beyond history cap'
	);
	adb_counts_check(
		array(
			2 => 5,
			1 => 8,
		) === adb_counts_read( 'actions', array( -2, 0, 1, 1 ) ) && array(
			2 => 0,
			1 => 3,
		) === adb_counts_read( 'non_terminal', array( -2, 0, 1, 1 ) ),
		'action absolute/zero policy and exact three states'
	);
	$groups[] = 'actual prepared grouped/scalar equality, independent state predicates, normalization and100/500-site budgets';
}

/**
 * Qualifies empty-array SQL errors, per-batch scalar recovery and zero convention.
 *
 * @return void
 */
function adb_counts_failure_cases() {
	global $wpdb, $queries, $faults, $trace, $tables, $groups;
	foreach ( array( 'snapshots', 'actions', 'non_terminal' ) as $kind ) {
		$table                    = 'snapshots' === $kind ? $tables['snapshots'] : $tables['actions'];
		$state                    = 'non_terminal' === $kind ? " AND state IN ('queued_for_dispatch', 'accepted', 'running')" : '';
		$faults['group'][ $kind ] = 1;
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Fixed owned table and source-controlled state clause; site value prepared.
		$raw = $wpdb->get_results( $wpdb->prepare( "SELECT dashboard_site_id, COUNT(*) AS row_count FROM {$table} WHERE dashboard_site_id IN (%d){$state} GROUP BY dashboard_site_id", 1 ), ARRAY_A );
		adb_counts_check( array() === $raw && '' !== $wpdb->last_error, 'genuine ARRAY_A SQL error is empty array with last_error' );
		$faults['group'][ $kind ] = 1;
		$before                   = $queries;
		$scalar_before            = $trace[ $kind . '_scalar' ] ?? 0;
		$actual                   = adb_counts_read( $kind, range( 1, 51 ) );
		adb_counts_check( '' === $wpdb->last_error && 52 === $queries - $before && 50 === $trace[ $kind . '_scalar' ] - $scalar_before, 'failed first group uses50 original scalar reads then valid tail, no second retry' );
		foreach ( range( 1, 51 ) as $site ) {
			adb_counts_check( adb_counts_expected( $kind, $site ) === $actual[ $site ], 'all recovered counts retain exact positive/zero values' );
		}
		$faults['group'][ $kind ]  = 1;
		$faults['scalar'][ $kind ] = 1;
		$before                    = $queries;
		adb_counts_check( array( 1 => 0 ) === adb_counts_read( $kind, array( 1 ) ) && '' !== $wpdb->last_error && 2 === $queries - $before, 'group and scalar failure retain original zero convention' );
		adb_counts_check( array( 1 => adb_counts_expected( $kind, 1 ) ) === adb_counts_read( $kind, array( 1 ) ) && '' === $wpdb->last_error, 'fresh grouped read recovers positive count and clears error' );
	}
	$groups[] = 'twelve physical owned read faults across three independent kinds, ARRAY_A error witness, bounded scalar recovery and zero convention';
}
