<?php
/**
 * Finite physical history/aggregation cases on the exact owned snapshot clone.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.DirectDatabaseQuery -- Only explicitly approved owned fixture rows.
/**
 * Asserts a fixed non-secret label, never raw SQL or row contents.
 *
 * @param bool   $condition Observed result.
 * @param string $label Fixed label.
 * @return void
 * @throws RuntimeException For failed proof assertions.
 */
function adb_history_check( $condition, $label ) {
	++$GLOBALS['checks'];
	if ( ! $condition ) {
		throw new RuntimeException( 'Owned history assertion: ' . $label ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Private fixed CLI label.
	}
}

/**
 * Seeds bounded synthetic rows with advancing, tied and regressed timestamps.
 *
 * @return void
 */
function adb_history_seed() {
	global $wpdb, $table, $groups;
	$base = strtotime( '2026-10-01 12:00:00 UTC' );
	foreach ( range( 1, 51 ) as $site ) {
		$count = 1 === $site ? 55 : 12;
		foreach ( range( 0, $count - 1 ) as $offset ) {
			$minute = $offset < 11 ? $offset : ( 11 === $offset ? 0 : -$offset );
			adb_history_check(
				1 === $wpdb->insert(
					$table,
					array(
						'id'                => $site * 100 + $offset + 1,
						'dashboard_site_id' => $site,
						'observed_at'       => gmdate( 'Y-m-d H:i:s', $base + $minute * 60 ),
						'overall_status'    => 0 === $offset % 2 ? 'working' : 'needs_attention',
						'payload_json'      => '{"owned":"π private marker"}',
					)
				) && '' === $wpdb->last_error,
				'bounded owned history row inserted'
			);
		}
	}
	foreach ( array( 52, 999 ) as $site ) {
		adb_history_check(
			1 === $wpdb->insert(
				$table,
				array(
					'id'                => $site * 100,
					'dashboard_site_id' => $site,
					'observed_at'       => '2099-01-01 00:00:00',
					'overall_status'    => 'working',
					'payload_json'      => '{}',
				)
			) && '' === $wpdb->last_error,
			'sparse and unrelated owned rows inserted'
		);
	}
	$groups[] = '657 synthetic rows including ties, regression, sparse and foreign sites';
}

/**
 * Compares real bulk and single-site statements, limits and query budgets.
 *
 * @return void
 */
function adb_history_read_cases() {
	global $wpdb, $queries, $groups;
	$repository = new Alynt_Drime_Backups_Dashboard_Snapshot_Repository();
	$before     = $queries;
	adb_history_check( array() === $repository->recent_by_site_ids( array() ) && array() === $repository->recent_by_site_ids( array( 0, '0' ) ) && $before === $queries, 'empty input never queries' );
	$histories = $repository->recent_by_site_ids( range( 1, 51 ), 10 );
	adb_history_check( '' === $wpdb->last_error && 2 === $queries - $before && 51 === count( $histories ), 'physical 51-site read uses two statements including single-site tail' );
	foreach ( range( 1, 51 ) as $site ) {
		$single = $repository->recent_for_site( $site, 10 );
		adb_history_check( '' === $wpdb->last_error && $single === $histories[ $site ], 'physical single and bulk histories equal per site' );
		adb_history_check( array_map( 'strval', range( $site * 100 + 11, $site * 100 + 2 ) ) === array_column( $single, 'id' ), 'each site has ten chronological rows, not highest-ID or global ten' );
		foreach ( $single as $row ) {
			adb_history_check( 11 === count( $row ) && ! isset( $row['payload_json'] ) && ! isset( $row['decoded_payload'] ) && (int) $row['dashboard_site_id'] === $site, 'physical summary projection and site separation' );
		}
	}
	$wide = $repository->recent_by_site_ids( array( '1', 1, -1, 2, 0, 53 ), 999 );
	adb_history_check( '' === $wpdb->last_error && array_keys( $wide ) === array( 1, 2, 53 ) && count( $wide[1] ) === 50 && count( $wide[2] ) === 12 && array() === $wide[53], 'normalization, independent cap and empty site' );
	adb_history_check( '212' === $wide[2][10]['id'] && '201' === $wide[2][11]['id'], 'equal timestamps retain descending ID tie break' );
	$narrow = $repository->recent_by_site_ids( array( 1, 2 ), 0 );
	adb_history_check( '' === $wpdb->last_error && count( $narrow[1] ) === 1 && count( $narrow[2] ) === 1, 'physical lower clamp is per site' );
	foreach ( array(
		100 => 2,
		500 => 10,
	) as $sites => $expected_queries ) {
		$before = $queries;
		$result = $repository->recent_by_site_ids( range( 1, $sites ) );
		adb_history_check( '' === $wpdb->last_error && $queries - $before === $expected_queries && count( $result ) === $sites && array() === $result[ $sites ], 'larger bounded physical batch budget and empty keys' );
	}
	$groups[] = 'real prepared single/multi/tail SELECT equality, chronology, projection and 100/500-site budgets';
}

/**
 * Qualifies the actual aggregate helper over physical reads, without collect().
 *
 * @return void
 */
function adb_history_aggregate_cases() {
	global $queries, $groups;
	$sites = array();
	foreach ( range( 1, 53 ) as $site ) {
		$sites[] = array(
			'id'          => $site,
			'archived_at' => 3 === $site ? '2026-10-27 00:00:00' : '',
		);
	}
	$sites[] = array( 'id' => 0 );
	$method  = new ReflectionMethod( Alynt_Drime_Backups_Dashboard_Diagnostics::class, 'attention_history_diagnostics' );
	$before  = $queries;
	$actual  = $method->invoke( new Alynt_Drime_Backups_Dashboard_Diagnostics(), $sites );
	adb_history_check( 2 === $queries - $before, 'actual aggregate helper uses two physical history statements' );
	adb_history_check(
		array(
			'records_with_history'         => 50,
			'recently_recovered_records'   => 50,
			'repeated_attention_records'   => 50,
			'recent_attention_transitions' => 200,
		) === $actual,
		'positive analytic aggregate oracle excludes archived, invalid, sparse and empty sites'
	);
	$reference = new class() extends Alynt_Drime_Backups_Dashboard_Snapshot_Repository {
		/**
		 * Reproduces the former per-site reader over the same physical fixture.
		 *
		 * @param array<int> $site_ids Site IDs.
		 * @param int        $limit Per-site bound.
		 * @return array<int,array>
		 */
		public function recent_by_site_ids( array $site_ids, $limit = 10 ) {
			$histories = array();
			foreach ( $site_ids as $site ) {
				$histories[ $site ] = $this->recent_for_site( $site, $limit );
			}
			return $histories;
		}
	};
	$before    = $queries;
	$legacy    = $method->invoke( new Alynt_Drime_Backups_Dashboard_Diagnostics( null, $reference ), $sites );
	adb_history_check( 52 === $queries - $before && $actual === $legacy, 'physical batched aggregate equals former 52-query strategy' );
	$groups[] = 'actual private aggregate helper, positive independent oracle and physical legacy-strategy equality';
}

/**
 * Injects two bounded syntax failures into owned SELECTs only, then recovers.
 *
 * @return void
 */
function adb_history_failure_cases() {
	global $wpdb, $queries, $fault_next, $faults, $groups;
	$repository = new Alynt_Drime_Backups_Dashboard_Snapshot_Repository();
	$fault_next = true;
	$before     = $queries;
	$failed     = $repository->recent_by_site_ids( array( 1 ) );
	adb_history_check( '' !== $wpdb->last_error && 1 === $queries - $before && array( 1 => array() ) === $failed, 'genuine failed owned SELECT returns empty history' );
	adb_history_check( 10 === count( $repository->recent_by_site_ids( array( 1 ) )[1] ) && '' === $wpdb->last_error, 'fresh read recovers without stale error or cache' );
	$fault_next = true;
	$before     = $queries;
	$result     = $repository->recent_by_site_ids( range( 1, 51 ) );
	adb_history_check( '' === $wpdb->last_error && 2 === $queries - $before && 10 === count( $result[51] ) && 2 === $faults, 'failed first batch does not prevent valid tail or add N+1 retries' );
	foreach ( range( 1, 50 ) as $site ) {
		adb_history_check( array() === $result[ $site ], 'every failed-batch key remains empty' );
	}
	$groups[] = 'two physical owned syntax failures, empty convention, later-batch continuation and recovery';
}
