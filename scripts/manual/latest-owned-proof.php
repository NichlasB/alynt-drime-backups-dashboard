<?php
/**
 * Approved Plugin Tester latest-selection proof, never application/CI invoked.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared -- Exact confirmed local owned fixture manifest only.
if ( PHP_SAPI !== 'cli' || 4 !== count( $argv ) || '--confirmed-plugin-tester-latest-proof' !== $argv[1] || ! preg_match( '/^adbls_[a-f0-9]{12}_$/D', $argv[2] ) || ! in_array( $argv[3], array( 'preflight', 'run' ), true ) || ! getenv( 'MYSQL_PWD' ) ) {
	fwrite( STDERR, "Requires confirmed Plugin Tester target, owned prefix and fixed mode\n" );
	exit( 1 );
}
define( 'ADB_LATEST_PROOF', true );
require __DIR__ . '/latest-proof-sql-scope.php';
require __DIR__ . '/latest-proof-core.php';
require __DIR__ . '/latest-proof-cases.php';
require __DIR__ . '/latest-proof-capabilities.php';
$checks  = 0;
$groups  = array();
$created = array();
$cleaned = false;
$started = microtime( true );
set_time_limit( 35 );
$exists  = function ( $table ) {
	global $wpdb;
	$result = $wpdb->get_row( $wpdb->prepare( 'SHOW TABLE STATUS WHERE Name = %s', $table ), ARRAY_A );
	adb_latest_check( '' === $wpdb->last_error, 'exact metadata read succeeds' );
	return $result;
};
$cleanup = function () use ( &$created, &$cleaned, $tables, $exists ) {
	global $wpdb;
	if ( $cleaned || ! $created ) {
		return;
	}
	foreach ( array_reverse( $created ) as $table ) {
		if ( $exists( $table ) ) {
			$count = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $table );
			adb_latest_check( '' === $wpdb->last_error && is_numeric( $count ) && (int) $count <= 64, 'bounded exact cleanup preview' );
			adb_latest_check( false !== $wpdb->query( 'DROP TABLE ' . $table ) && '' === $wpdb->last_error, 'exact owned fixture dropped' );
		}
	}
	foreach ( $tables as $table ) {
		adb_latest_check( null === $exists( $table ), 'all owned targets absent after cleanup' );
	}
	$cleaned = true;
};
register_shutdown_function(
	function () use ( $cleanup ) {
		try {
			$cleanup();
		} catch ( Throwable $error ) {
			fwrite( STDERR, "Owned latest-selection cleanup incomplete; inspect exact manifest\n" );
		}
	}
);
try {
	$identity = $wpdb->get_row( 'SELECT DATABASE() AS db, @@port AS port, VERSION() AS version', ARRAY_A );
	adb_latest_check( '' === $wpdb->last_error && 'local' === $identity['db'] && '10004' === (string) $identity['port'] && 0 === strpos( $identity['version'], '10.6.23-MariaDB' ), 'exact confirmed database identity' );
	foreach ( $tables as $table ) {
		adb_latest_check( null === $exists( $table ), 'all exact targets absent before any fixture' );
	}
	adb_latest_check( 'Owned π' === sanitize_text_field( 'Owned π' ), 'genuine UTF-8 core preflight' );
	adb_latest_check( class_exists( 'Alynt_Drime_Backups_Dashboard_Plugin' ) && class_exists( 'Alynt_Drime_Backups_Dashboard_Admin_Page' ), 'actual root loader available' );
	adb_latest_check( 3 === $queries, 'preflight only identity and owned absence reads' );
	if ( 'preflight' === $argv[3] ) {
		echo json_encode(
			array(
				'checks'           => $checks,
				'queries'          => $queries,
				'fixtures_created' => 0,
				'preflight'        => true,
			)
		) . "\n";
		exit;
	}
	foreach ( $tables as $index => $table ) {
		$source = array( 'wp_alynt_drime_dashboard_sites', 'wp_alynt_drime_dashboard_snapshots' )[ $index ];
		adb_latest_query( 'CREATE TABLE ' . $table . ' LIKE ' . $source );
		$created[]      = $table;
		$fixture_status = $exists( $table );
		adb_latest_check( 'InnoDB' === $fixture_status['Engine'] && '0' === (string) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $table ), 'empty genuine InnoDB schema-only clone' );
	}
	$groups[] = 'exact target, genuine core and empty schema-only fixtures';
	adb_latest_selector_cases();
	adb_latest_capability_cases();
	adb_latest_check( 0 === $http, 'no HTTP attempted' );
	$cleanup();
	adb_latest_check( microtime( true ) - $started < 30, 'whole proof bounded wall time' );
	echo json_encode(
		array(
			'checks'         => $checks,
			'groups'         => $groups,
			'queries'        => $queries,
			'http_attempts'  => $http,
			'cleaned'        => $cleaned,
			'tables_removed' => count( $created ),
			'seconds'        => round( microtime( true ) - $started, 3 ),
		)
	) . "\n";
} finally {
	$cleanup();
	$wpdb->close();
}
