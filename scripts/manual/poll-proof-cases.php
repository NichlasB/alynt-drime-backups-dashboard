<?php
/**
 * Actual repository/wpdb cases against the owned fixture, no application requests.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared -- Fixed manifest-guarded manual fixtures; no application tables or request path.

/**
 * Resets at most two owned synthetic rows and returns genuine repository data.
 *
 * @param array $overrides Fixed fixture values.
 * @return array
 */
function adb_poll_fixture( array $overrides = array() ) {
	global $wpdb, $table, $repository;
	$count = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $table );
	adb_poll_check( '' === $wpdb->last_error && null !== $count && (int) $count <= 2, 'successful bounded owned reset preview' );
	adb_poll_check( false !== $wpdb->query( 'DELETE FROM ' . $table ), 'owned fixture reset' );
	$row = array_merge(
		array(
			'id'                            => 7,
			'public_id'                     => 'AAAAAAAA-AAAA-4AAA-8AAA-AAAAAAAAAAAA',
			'enrollment_status'             => 'active',
			'polling_key_id'                => 'Owned-Key-A',
			'polling_secret_ciphertext'     => "Owned-Cipher-A'\\π",
			'action_key_id'                 => 'Owned-Action',
			'action_private_key_ciphertext' => 'Owned-Action-Cipher',
			'created_at'                    => '2099-01-01 00:00:00',
			'updated_at'                    => '2099-01-01 00:00:00',
		),
		$overrides
	);
	adb_poll_check( 1 === $wpdb->insert( $table, $row ), 'genuine fixture insert' );
	$captured = $repository->get( (int) $row['id'] );
	adb_poll_check( is_array( $captured ) && $row['polling_secret_ciphertext'] === $captured['polling_secret_ciphertext'], 'genuine prepare escaping round trip' );
	return $captured;
}

/**
 * Executes the actual completion method with captured data.
 *
 * @param array $captured Captured row.
 * @param bool  $failure Whether recording failure.
 * @return bool|WP_Error
 */
function adb_poll_complete( array $captured, $failure = false ) {
	global $repository;
	return $failure
		? $repository->mark_poll_failure( 7, 'transport_failed', 'Owned failure', '2099-01-02 00:00:00', 2, $captured )
		: $repository->mark_poll_success( 7, 'working', '1.2.3', '2099-01-02 00:00:00', $captured );
}

/**
 * Checks stale rejection without exposing stored data or raw SQL.
 *
 * @param mixed $result Actual result.
 * @return void
 */
function adb_poll_stale( $result ) {
	adb_poll_check( is_wp_error( $result ) && 'poll_completion_stale' === $result->get_error_code() && null === $result->get_error_data(), 'explicit safe stale rejection' );
}

/**
 * Covers real prepared writes, unchanged rows, corruption and physical SQL errors.
 *
 * @return void
 */
function adb_poll_storage_cases() {
	global $wpdb, $table, $repository, $groups, $queries, $reads, $fault, $before_read;
	foreach ( array( false, true ) as $failure ) {
		$captured = adb_poll_fixture( array( 'enrollment_status' => $failure ? 'active' : 'awaiting_first_poll' ) );
		adb_poll_check( true === adb_poll_complete( $captured, $failure ), 'actual positive completion' );
		$row = $repository->get( 7 );
		adb_poll_check( ( $failure ? 'needs_attention' : 'working' ) === $row['overall_status'] && 'active' === $row['enrollment_status'], 'physical completion fields' );
		$before_reads = $reads;
		// Bounded extra call handles a wall-clock second boundary, not a failed assertion retry.
		for ( $attempt = 0; $attempt < 2 && $reads === $before_reads; ++$attempt ) {
			$before_queries = $queries;
			adb_poll_check( true === adb_poll_complete( $captured, $failure ), 'legitimate unchanged completion' );
			adb_poll_check( $queries - $before_queries <= 2, 'bounded completion query count' );
		}
		adb_poll_check( $reads > $before_reads, 'actual zero-affected-row verification executed' );
		$before = $queries;
		adb_poll_stale( adb_poll_complete( array(), $failure ) );
		adb_poll_check( $before === $queries, 'invalid context rejects before SQL' );
		$captured = adb_poll_fixture();
		adb_poll_check( true === $repository->revoke_local( 7 ), 'actual local revocation' );
		$revoked = $repository->get( 7 );
		adb_poll_stale( adb_poll_complete( $captured, $failure ) );
		adb_poll_check( $revoked === $repository->get( 7 ), 'revoked row remains byte-identical' );
		foreach ( array( 'public_id', 'polling_key_id', 'polling_secret_ciphertext' ) as $field ) {
			$captured = adb_poll_fixture();
			$changed  = strtolower( $captured[ $field ] );
			adb_poll_check( 1 === $wpdb->update( $table, array( $field => $changed ), array( 'id' => 7 ) ), 'owned credential identity change' );
			$plain = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . $table . ' WHERE ' . $field . ' = %s', $captured[ $field ] ) );
			adb_poll_check( '1' === $plain, 'real case-insensitive collation control' );
			$before = $repository->get( 7 );
			adb_poll_stale( adb_poll_complete( $captured, $failure ) );
			adb_poll_check( $before === $repository->get( 7 ), 'binary comparison prevents stale overwrite' );
		}
		$captured = adb_poll_fixture();
		adb_poll_check( 1 === $wpdb->update( $table, array( 'enrollment_status' => 'pending' ), array( 'id' => 7 ) ), 'owned lifecycle transition' );
		adb_poll_stale( adb_poll_complete( $captured, $failure ) );
		$captured = adb_poll_fixture();
		adb_poll_check( 1 === $wpdb->query( 'DELETE FROM ' . $table . ' WHERE id=7' ), 'owned missing-row preview and delete' );
		adb_poll_stale( adb_poll_complete( $captured, $failure ) );
		$captured = adb_poll_fixture();
		$fault    = 'write';
		adb_poll_check( false === adb_poll_complete( $captured, $failure ) && '' !== $wpdb->last_error, 'real server write error stays false' );
		$fault = '';
		adb_poll_check( true === adb_poll_complete( $captured, $failure ), 'write error recovery' );
		$fault        = 'read';
		$before_reads = $reads;
		$result       = true;
		for ( $attempt = 0; $attempt < 2 && $reads === $before_reads; ++$attempt ) {
			$result = adb_poll_complete( $captured, $failure );
		}
		adb_poll_check( $reads > $before_reads && false === $result && '' !== $wpdb->last_error, 'real zero-row read error stays false' );
		$fault = '';
		foreach ( array( 'revoke', 'persisted-value' ) as $interleaving ) {
			$captured = adb_poll_fixture();
			adb_poll_check( true === adb_poll_complete( $captured, $failure ), 'readback interleaving positive prerequisite' );
			$before_read = function () use ( $interleaving, $table, $repository, $wpdb ) {
				$changed = 'revoke' === $interleaving ? $repository->revoke_local( 7 ) : 1 === $wpdb->update( $table, array( 'overall_status' => 'owned_changed' ), array( 'id' => 7 ) );
				adb_poll_check( true === $changed, 'actual owned change between zero write and readback' );
			};
			$result      = true;
			for ( $attempt = 0; $attempt < 2 && $before_read; ++$attempt ) {
				$result = adb_poll_complete( $captured, $failure );
			}
			adb_poll_check( null === $before_read, 'actual zero-row readback boundary reached' );
			adb_poll_stale( $result );
		}
		$groups[] = $failure ? 'failure writes and zero-row SQL' : 'success and first-poll zero-row SQL';
	}
	foreach ( array( null, '' ) as $empty_credential ) {
		$captured = adb_poll_fixture(
			array(
				'polling_key_id'            => $empty_credential,
				'polling_secret_ciphertext' => $empty_credential,
			)
		);
		adb_poll_stale( adb_poll_complete( $captured ) );
		adb_poll_check( true === adb_poll_complete( $captured, true ), 'explicit empty credential failure context' );
	}
	$captured = adb_poll_fixture();
	adb_poll_fixture( array( 'id' => 8 ) );
	adb_poll_stale( adb_poll_complete( $captured ) );
	adb_poll_check( null === $repository->get( 8 )['last_seen_at'], 'primary key fence protects other physical row' );
	$groups[] = 'nullable context';
}

/**
 * Observes a real InnoDB waiter against this exact owning connection, not a sleep.
 *
 * @param array $child Owned worker resource/manifest.
 * @return void
 * @throws RuntimeException If no actual blocking witness is observed within budget.
 */
function adb_poll_witness_wait( array $child ) {
	global $wpdb;
	$sql      = 'SELECT COUNT(*) FROM information_schema.INNODB_LOCK_WAITS w JOIN information_schema.INNODB_TRX r ON r.trx_id=w.requesting_trx_id JOIN information_schema.INNODB_TRX b ON b.trx_id=w.blocking_trx_id WHERE r.trx_mysql_thread_id=' . (int) $child['connection_id'] . ' AND b.trx_mysql_thread_id=CONNECTION_ID()';
	$deadline = microtime( true ) + 1.5;
	do {
		$count = $wpdb->get_var( $sql );
		adb_poll_check( '' === $wpdb->last_error, 'lock-witness query succeeds' );
		if ( '1' === $count ) {
			adb_poll_check( true, 'real owned InnoDB blocking relationship' );
			return;
		}
		// MariaDB10.6.23 trx0i_s.cc requires >100ms idle between cache reads.
		// Sampling too quickly freezes the old snapshot; the witness itself is still required.
		usleep( (int) min( 200000, max( 0, ( $deadline - microtime( true ) ) * 1000000 ) ) );
	} while ( microtime( true ) < $deadline );
	$process       = proc_get_status( $child['process'] );
	$result_path   = $child['directory'] . '/result.json';
	$worker_result = is_file( $result_path ) ? json_decode( adb_poll_read_marker( $result_path ), true ) : array();
	$stage_path    = $child['directory'] . '/stage.json';
	$stage         = is_file( $stage_path ) ? json_decode( adb_poll_read_marker( $stage_path ), true ) : array();
	$server_state  = $wpdb->get_var( 'SELECT STATE FROM information_schema.PROCESSLIST WHERE ID=' . (int) $child['connection_id'] . ' AND DB=DATABASE()' );
	$transactions  = $wpdb->get_results( 'SELECT trx_mysql_thread_id,trx_state,trx_id,trx_rows_locked FROM information_schema.INNODB_TRX WHERE trx_mysql_thread_id IN (CONNECTION_ID(),' . (int) $child['connection_id'] . ')', ARRAY_A );
	$stderr        = file_get_contents( $child['directory'] . '/stderr.log', false, null, 0, 4096 );
	$label         = 'no fixed error label';
	if ( preg_match( '/(?:Assertion failed: |Uncaught (?:Error|RuntimeException): )([A-Za-z0-9 _-]+)/', $stderr, $match ) ) {
		$label = $match[1];
	}
	throw new RuntimeException( 'Lock witness missing; running=' . (int) $process['running'] . '; result=' . ( isset( $worker_result['type'] ) ? sanitize_key( $worker_result['type'] ) : 'none' ) . '; stage=' . ( isset( $stage['stage'] ) ? sanitize_key( $stage['stage'] ) : 'none' ) . '; server=' . sanitize_key( (string) $server_state ) . '; owned_transactions=' . json_encode( $transactions ) . '; diagnostic=' . $label ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Fixed/sanitized private CLI diagnostic and owned transaction IDs/states only, no raw SQL/fixture data.
}

/**
 * Qualifies both serialization orders and one real held-lock timeout.
 *
 * @param string $prefix Owned table prefix.
 * @return void
 */
function adb_poll_race_cases( $prefix ) {
	global $wpdb, $repository, $groups, $worker_queries;
	foreach ( array( 'success', 'failure', 'revoke-success', 'revoke-failure', 'timeout' ) as $order ) {
		$captured     = adb_poll_fixture();
		$revoke_first = in_array( $order, array( 'success', 'failure', 'timeout' ), true );
		$failure      = in_array( $order, array( 'failure', 'revoke-failure' ), true );
		$child        = adb_poll_spawn( $prefix, $revoke_first ? ( $failure ? 'failure' : 'success' ) : 'revoke' );
		try {
			adb_poll_check( false !== $wpdb->query( 'START TRANSACTION' ), 'owned transaction begins' );
			adb_poll_check( true === ( $revoke_first ? $repository->revoke_local( 7 ) : adb_poll_complete( $captured, $failure ) ), 'first actual operation holds row lock' );
			adb_poll_check( '1' === $wpdb->get_var( 'SELECT @@in_transaction' ), 'first actor really remains in an owned transaction' );
			adb_poll_check( false !== file_put_contents( $child['directory'] . '/go.txt', $prefix, LOCK_EX ), 'owned worker released' );
			adb_poll_witness_wait( $child );
			if ( 'timeout' !== $order ) {
				adb_poll_check( false !== $wpdb->query( 'COMMIT' ), 'first actor commits before waiter completes' );
			}
			$result   = adb_poll_result( $child );
			$expected = 'timeout' === $order ? 'storage' : ( $revoke_first ? 'stale' : 'success' );
			adb_poll_check( $expected === $result['type'], 'actual waiting operation has required outcome' );
			adb_poll_check( ( 'timeout' === $order ) === $result['query_error'], 'server error only on deliberate held timeout' );
			adb_poll_check( ( 'timeout' === $order ) === $result['lock_timeout'], 'held failure is the actual server lock-wait timeout' );
			$worker_queries += $result['queries'];
			if ( 'timeout' === $order ) {
				adb_poll_check( false !== $wpdb->query( 'COMMIT' ), 'held revocation commits after bounded timeout' );
			}
			$row = $repository->get( 7 );
			adb_poll_check( 'revoked' === $row['enrollment_status'] && null === $row['polling_key_id'] && null === $row['polling_secret_ciphertext'] && null === $row['action_private_key_ciphertext'] && null === $row['next_poll_at'], 'revocation wins and credentials stay cleared' );
		} finally {
			adb_poll_reap( $child );
			$wpdb->query( 'ROLLBACK' );
		}
		$groups[] = 'ordered ' . $order;
	}
}
