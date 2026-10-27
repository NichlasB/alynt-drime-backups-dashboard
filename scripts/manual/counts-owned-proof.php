<?php
/**
 * Approved Plugin Tester count proof; never loaded by application or CI.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared -- Exact two owned fixtures only, no application writes.
if ( PHP_SAPI !== 'cli' || 4 !== count( $argv ) || '--confirmed-plugin-tester-counts-proof' !== $argv[1] || ! preg_match( '/^adbc_[a-f0-9]{12}_$/D', $argv[2] ) || ! in_array( $argv[3], array( 'preflight', 'run' ), true ) || ! getenv( 'MYSQL_PWD' ) ) {
	fwrite( STDERR, "Requires confirmed local target, exact owned namespace and fixed mode\n" );
	exit( 1 );
}
define( 'ADB_COUNTS_PROOF', true );
require __DIR__ . '/counts-proof-sql-scope.php';
require __DIR__ . '/counts-proof-core.php';
require __DIR__ . '/counts-proof-cases.php';
require __DIR__ . '/counts-proof-aggregation.php';
$checks  = 0;
$groups  = array();
$created = array();
$cleaned = false;
$started = microtime( true );
set_time_limit( 35 );
$exists  = function ( $table ) {
	global $wpdb;
	$result = $wpdb->get_row( $wpdb->prepare( 'SHOW TABLE STATUS WHERE Name = %s', $table ), ARRAY_A );
	adb_counts_check( '' === $wpdb->last_error, 'exact owned metadata succeeds' );
	return $result;
};
$cleanup = function () use ( &$created, &$cleaned, &$cleaning, $tables, $exists ) {
	global $wpdb;
	if ( ! $created || $cleaned ) {
		return;
	}
	$cleaning = true;
	foreach ( array_reverse( $created ) as $table ) {
		if ( $exists( $table ) ) {
			$count = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $table );
			adb_counts_check( '' === $wpdb->last_error && is_numeric( $count ) && (int) $count <= 1024, 'bounded exact cleanup preview' );
			adb_counts_check( false !== $wpdb->query( 'DROP TABLE ' . $table ) && '' === $wpdb->last_error, 'exact owned fixture dropped' );
		}
	}
	foreach ( $tables as $table ) {
		adb_counts_check( null === $exists( $table ), 'owned target absent after cleanup' );
	}
	$cleaned = true;
};
register_shutdown_function(
	function () use ( $cleanup ) {
		try {
			$cleanup();
		} catch ( Throwable $error ) {
				fwrite( STDERR, "Owned count cleanup incomplete; inspect exact namespace\n" ); }
	}
);
try {
	$identity = $wpdb->get_row( 'SELECT DATABASE() AS db, @@port AS port, VERSION() AS version', ARRAY_A );
	adb_counts_check( '' === $wpdb->last_error && 'local' === $identity['db'] && '10004' === (string) $identity['port'] && 0 === strpos( $identity['version'], '10.6.23-MariaDB' ), 'confirmed database identity' );
	foreach ( $tables as $table ) {
		adb_counts_check( null === $exists( $table ), 'all targets absent before any CREATE' );
	}
	adb_counts_check( 'Owned π' === sanitize_text_field( 'Owned π' ) && class_exists( 'Alynt_Drime_Backups_Dashboard_Diagnostics' ), 'genuine core and actual root loader' );
	adb_counts_check( 3 === $queries, 'identity and exact absence only' );
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
	foreach ( $tables as $kind => $table ) {
		$created[] = $table;
		adb_counts_check( false !== $wpdb->query( 'CREATE TABLE ' . $table . ' LIKE wp_alynt_drime_dashboard_' . $kind ) && '' === $wpdb->last_error, 'schema-only owned clone created' );
		$fixture_status = $exists( $table );
		adb_counts_check( 'InnoDB' === $fixture_status['Engine'] && '0' === (string) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $table ), 'empty InnoDB schema-only fixture' );
	}
	$groups[] = 'confirmed target, genuine core and two empty owned schema-only clones';
	adb_counts_seed();
	adb_counts_read_cases();
	adb_counts_aggregate_cases();
	adb_counts_failure_cases();
	adb_counts_check( 0 === $http && 12 === $physical_faults, 'zero HTTP and exactly twelve planned owned read faults' );
	$cleanup();
	adb_counts_check( microtime( true ) - $started < 30, 'bounded total proof time' );
	echo json_encode(
		array(
			'checks'          => $checks,
			'groups'          => $groups,
			'queries'         => $queries,
			'physical_faults' => $physical_faults,
			'http_attempts'   => $http,
			'cleaned'         => $cleaned,
			'tables_removed'  => count( $created ),
			'seconds'         => round( microtime( true ) - $started, 3 ),
		)
	) . "\n";
} finally {
	$cleanup();
	$wpdb->close();
}
