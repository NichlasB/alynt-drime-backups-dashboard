<?php
/**
 * Explicitly approved Plugin Tester fixture proof. Never invoked by CI/application.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared -- Fixed exact owned-table manifest, explicit local target gate, no application writes or public entry.
if ( PHP_SAPI !== 'cli' || ! in_array( count( $argv ), array( 4, 5 ), true ) || '--confirmed-plugin-tester-owned-proof' !== $argv[1] || ! preg_match( '/^adbrp_[a-f0-9]{12}_$/D', $argv[2] ) || ! in_array( $argv[3], array( 'preflight', 'parent', 'success', 'failure', 'revoke' ), true ) || ! getenv( 'MYSQL_PWD' ) ) {
	fwrite( STDERR, "Requires exact target approval, owned prefix, fixed role and inherited MYSQL_PWD\n" );
	exit( 1 );
}
define( 'ADB_POLL_PROOF', true );
require __DIR__ . '/poll-proof-sql-scope.php';
require __DIR__ . '/poll-proof-core.php';
require __DIR__ . '/poll-proof-process.php';
require __DIR__ . '/poll-proof-cases.php';
$repository     = new Alynt_Drime_Backups_Dashboard_Site_Repository();
$checks         = 0;
$groups         = array();
$worker_queries = 0;
$armed          = false;
$cleaned        = false;
$started        = microtime( true );

/**
 * Counts fixed-label assertions without exposing SQL or fixture data.
 *
 * @param bool   $condition Observed condition.
 * @param string $label Fixed description.
 * @return void
 * @throws RuntimeException On assertion failure.
 */
function adb_poll_check( $condition, $label ) {
	global $checks;
	++$checks;
	if ( ! $condition ) {
		throw new RuntimeException( 'Assertion failed: ' . $label ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Fixed-label private CLI proof, not HTML.
	}
}

if ( in_array( $argv[3], array( 'success', 'failure', 'revoke' ), true ) ) {
	if ( 5 !== count( $argv ) || realpath( dirname( $argv[4] ) ) !== realpath( sys_get_temp_dir() ) || ! preg_match( '/^' . preg_quote( $argv[2], '/' ) . '[a-f0-9]{12}$/D', basename( $argv[4] ) ) || is_link( $argv[4] ) || ( realpath( $argv[4] ) !== $argv[4] && str_replace( '\\', '/', realpath( $argv[4] ) ) !== str_replace( '\\', '/', $argv[4] ) ) ) {
		throw new RuntimeException( 'Worker marker manifest rejected' );
	}
	try {
		$stage_path = $argv[4] . '/stage.json';
		$captured   = $repository->get( 7 );
		adb_poll_check( is_array( $captured ), 'worker captures owned row before first actor' );
		$connection = (int) $wpdb->get_var( 'SELECT CONNECTION_ID()' );
		adb_poll_check( 0 < $connection && '' === $wpdb->last_error, 'owned worker connection witness' );
		adb_poll_check( false !== file_put_contents( $argv[4] . '/ready.json', json_encode( array( 'connection_id' => $connection ) ), LOCK_EX ), 'worker readiness marker' );
		file_put_contents( $stage_path, json_encode( array( 'stage' => 'waiting-go' ) ), LOCK_EX );
		adb_poll_wait_marker( $argv[4] . '/go.txt', 5 );
		adb_poll_check( adb_poll_read_marker( $argv[4] . '/go.txt' ) === $argv[2], 'exact worker release marker' );
		$result  = 'revoke' === $argv[3] ? $repository->revoke_local( 7 ) : adb_poll_complete( $captured, 'failure' === $argv[3] );
		$payload = array(
			'type'         => is_wp_error( $result ) ? $result->get_error_code() : ( true === $result ? 'success' : 'storage' ),
			'query_error'  => '' !== $wpdb->last_error,
			'lock_timeout' => false !== stripos( $wpdb->last_error, 'Lock wait timeout exceeded' ),
			'queries'      => $queries,
		);
		if ( 'poll_completion_stale' === $payload['type'] ) {
			$payload['type'] = 'stale';
		}
		adb_poll_check( false !== file_put_contents( $argv[4] . '/result.json', json_encode( $payload ), LOCK_EX ), 'worker result marker' );
	} finally {
		$wpdb->close();
	}
	exit;
}

$cleanup = function () use ( &$armed, &$cleaned, &$fault, $table ) {
	global $wpdb;
	if ( ! $armed || $cleaned ) {
		return;
	}
	$fault = '';
	$wpdb->query( 'ROLLBACK' );
	$exists = $wpdb->get_row( $wpdb->prepare( 'SHOW TABLE STATUS WHERE Name = %s', $table ), ARRAY_A );
	adb_poll_check( '' === $wpdb->last_error, 'cleanup metadata read succeeded' );
	if ( $exists ) {
		$count = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $table );
		adb_poll_check( '' === $wpdb->last_error && null !== $count && (int) $count <= 2, 'exact owned cleanup row preview' );
		adb_poll_check( false !== $wpdb->query( 'DROP TABLE ' . $table ), 'owned table removed' );
	}
	adb_poll_check( null === $wpdb->get_row( $wpdb->prepare( 'SHOW TABLE STATUS WHERE Name = %s', $table ) ) && '' === $wpdb->last_error, 'successful metadata proves fixture absence' );
	$cleaned = true;
};
register_shutdown_function(
	function () use ( $cleanup ) {
		try {
				$cleanup();
		} catch ( Throwable $error ) {
			fwrite( STDERR, "Owned cleanup incomplete; inspect exact manifest\n" );
		}
	}
);
try {
	adb_poll_check( null === $wpdb->get_row( $wpdb->prepare( 'SHOW TABLE STATUS WHERE Name = %s', $table ) ) && '' === $wpdb->last_error, 'fresh owned table absent' );
	adb_poll_check( function_exists( 'proc_open' ), 'owned process API is available' );
	adb_poll_stale( $repository->mark_poll_success( 7, 'working' ) );
	adb_poll_check( 'Owned π' === sanitize_text_field( 'Owned π' ), 'genuine charset-dependent sanitization is storage-free' );
	adb_poll_check( 1 === $queries, 'core translation and invalid admission cause no protected read' );
	if ( 'preflight' === $argv[3] ) {
		echo json_encode(
			array(
				'checks'           => $checks,
				'queries'          => $queries,
				'fixtures_created' => 0,
			)
		) . "\n";
		exit;
	}
	$armed = true;
	adb_poll_check( false !== $wpdb->query( 'CREATE TABLE ' . $table . ' LIKE wp_alynt_drime_dashboard_sites' ), 'owned schema-only clone created' );
	$fixture_status = $wpdb->get_row( $wpdb->prepare( 'SHOW TABLE STATUS WHERE Name = %s', $table ), ARRAY_A );
	adb_poll_check( 'InnoDB' === $fixture_status['Engine'] && false !== strpos( $fixture_status['Collation'], '_ci' ), 'genuine transactional case-insensitive fixture profile' );
	adb_poll_check( 26 === count( $wpdb->get_col( 'SHOW FULL COLUMNS FROM ' . $table ) ) && '' === $wpdb->last_error, 'installed schema shape preserved in owned clone' );
	adb_poll_storage_cases();
	adb_poll_race_cases( $argv[2] );
	$cleanup();
	adb_poll_check( microtime( true ) - $started < 40, 'whole proof wall-time budget' );
	echo json_encode(
		array(
			'checks'         => $checks,
			'groups'         => $groups,
			'queries'        => $queries,
			'worker_queries' => $worker_queries,
			'cleaned'        => $cleaned,
			'seconds'        => round( microtime( true ) - $started, 3 ),
		)
	) . "\n";
} finally {
	$cleanup();
	$wpdb->close();
}
