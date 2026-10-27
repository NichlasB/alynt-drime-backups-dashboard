<?php
/**
 * Explicitly approved Plugin Tester owned proof; not a runtime or CI entry.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared -- Fixed owned fixture only; no application writes.
if ( PHP_SAPI !== 'cli' || 4 !== count( $argv ) || '--confirmed-plugin-tester-history-proof' !== $argv[1] || ! preg_match( '/^adbh_[a-f0-9]{12}_$/D', $argv[2] ) || ! in_array( $argv[3], array( 'preflight', 'run' ), true ) || ! getenv( 'MYSQL_PWD' ) ) {
	fwrite( STDERR, "Requires confirmed local target, owned namespace and fixed mode\n" );
	exit( 1 );
}
define( 'ADB_HISTORY_PROOF', true );
require __DIR__ . '/history-proof-sql-scope.php';
require __DIR__ . '/history-proof-core.php';
require __DIR__ . '/history-proof-cases.php';
$checks  = 0;
$groups  = array();
$created = false;
$cleaned = false;
$started = microtime( true );
set_time_limit( 35 );
$exists  = function () use ( $table ) {
	global $wpdb;
	$result = $wpdb->get_row( $wpdb->prepare( 'SHOW TABLE STATUS WHERE Name = %s', $table ), ARRAY_A );
	adb_history_check( '' === $wpdb->last_error, 'exact owned metadata succeeds' );
	return $result;
};
$cleanup = function () use ( &$created, &$cleaned, $table, $exists ) {
	global $wpdb;
	if ( ! $created || $cleaned ) {
		return;
	}
	if ( $exists() ) {
		$count = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $table );
		adb_history_check( '' === $wpdb->last_error && is_numeric( $count ) && (int) $count <= 1024, 'bounded exact cleanup preview' );
		adb_history_check( false !== $wpdb->query( 'DROP TABLE ' . $table ) && '' === $wpdb->last_error, 'exact owned fixture dropped' );
	}
	adb_history_check( null === $exists(), 'exact owned fixture absent after cleanup' );
	$cleaned = true;
};
register_shutdown_function(
	function () use ( $cleanup ) {
		try {
			$cleanup();
		} catch ( Throwable $error ) {
			fwrite( STDERR, "Owned history cleanup incomplete; inspect exact namespace\n" );
		}
	}
);
try {
	$identity = $wpdb->get_row( 'SELECT DATABASE() AS db, @@port AS port, VERSION() AS version', ARRAY_A );
	adb_history_check( '' === $wpdb->last_error && 'local' === $identity['db'] && '10004' === (string) $identity['port'] && 0 === strpos( $identity['version'], '10.6.23-MariaDB' ), 'confirmed local database identity' );
	adb_history_check( null === $exists(), 'owned target absent before creation' );
	adb_history_check( 'Owned π' === sanitize_text_field( 'Owned π' ), 'genuine core UTF-8 preflight' );
	adb_history_check( class_exists( 'Alynt_Drime_Backups_Dashboard_Diagnostics' ), 'actual root loader available' );
	adb_history_check( 2 === $queries, 'preflight only identity and absence reads' );
	if ( 'preflight' === $argv[3] ) {
		echo json_encode(
			array(
				'preflight'        => true,
				'checks'           => $checks,
				'queries'          => $queries,
				'fixtures_created' => 0,
			)
		) . "\n";
		exit;
	}
	$created = true; // Exact absent namespace is now owned, even if CREATE reports an uncertain result.
	adb_history_check( false !== $wpdb->query( 'CREATE TABLE ' . $table . ' LIKE wp_alynt_drime_dashboard_snapshots' ) && '' === $wpdb->last_error, 'schema-only fixture created' );
	$fixture_status = $exists();
	adb_history_check( 'InnoDB' === $fixture_status['Engine'] && '0' === (string) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $table ), 'empty genuine InnoDB clone' );
	$groups[] = 'confirmed local target, genuine core and empty schema-only fixture';
	adb_history_seed();
	adb_history_read_cases();
	adb_history_aggregate_cases();
	adb_history_failure_cases();
	adb_history_check( 0 === $http, 'zero HTTP attempted' );
	$cleanup();
	adb_history_check( microtime( true ) - $started < 30, 'bounded total proof time' );
	echo json_encode(
		array(
			'checks'          => $checks,
			'groups'          => $groups,
			'queries'         => $queries,
			'physical_faults' => $faults,
			'http_attempts'   => $http,
			'cleaned'         => $cleaned,
			'tables_removed'  => 1,
			'seconds'         => round( microtime( true ) - $started, 3 ),
		)
	) . "\n";
} finally {
	$cleanup();
	$wpdb->close();
}
