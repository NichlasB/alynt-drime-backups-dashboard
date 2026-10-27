<?php
/**
 * Approved Plugin Tester retention proof, never invoked by CI/application.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared -- Fixed confirmed local target and exact owned table manifest only.
if ( PHP_SAPI !== 'cli' || 4 !== count( $argv ) || '--confirmed-plugin-tester-retention-proof' !== $argv[1] || ! preg_match( '/^adbrt_[a-f0-9]{12}_$/D', $argv[2] ) || ! in_array( $argv[3], array( 'preflight', 'run' ), true ) || ! getenv( 'MYSQL_PWD' ) ) {
	fwrite( STDERR, "Requires confirmed Plugin Tester target, owned prefix and fixed mode\n" );
	exit( 1 );
}
define( 'ADB_RETENTION_PROOF', true );
require __DIR__ . '/retention-proof-sql-scope.php';
require __DIR__ . '/retention-proof-core.php';
require __DIR__ . '/retention-proof-fixtures.php';
require __DIR__ . '/retention-proof-batches.php';
require __DIR__ . '/retention-proof-reporting.php';
$checks  = 0;
$groups  = array();
$created = array();
$cleaned = false;
$started = microtime( true );
set_time_limit( 45 );
$exists  = function ( $table ) {
	global $wpdb;
	$result = $wpdb->get_row( $wpdb->prepare( 'SHOW TABLE STATUS WHERE Name = %s', $table ), ARRAY_A );
	adb_retention_check( '' === $wpdb->last_error, 'successful exact fixture metadata read' );
	return $result;
};
$cleanup = function () use ( &$created, &$cleaned, $tables, $exists ) {
	global $wpdb, $faults, $before_delete;
	if ( $cleaned || ! $created ) {
		return;
	}
	$faults        = array();
	$before_delete = null;
	foreach ( array_reverse( $created ) as $table ) {
		if ( $exists( $table ) ) {
			$count = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $table );
			adb_retention_check( '' === $wpdb->last_error && is_numeric( $count ) && (int) $count <= 6005, 'successful bounded exact cleanup preview' );
			adb_retention_check( false !== $wpdb->query( 'DROP TABLE ' . $table ) && '' === $wpdb->last_error, 'owned fixture dropped' );
		}
	}
	foreach ( $tables as $table ) {
		adb_retention_check( null === $exists( $table ), 'successful metadata proves all fixture targets absent' );
	}
	$cleaned = true;
};
register_shutdown_function(
	function () use ( $cleanup ) {
		try {
			$cleanup();
		} catch ( Throwable $error ) {
			fwrite( STDERR, "Owned retention cleanup incomplete; inspect exact manifest\n" );
		}
	}
);
try {
	foreach ( $tables as $table ) {
		adb_retention_check( null === $exists( $table ), 'every target absent before any fixture creation' );
	}
	adb_retention_check( 'Owned π' === sanitize_text_field( 'Owned π' ), 'genuine core UTF-8 sanitization preflight' );
	adb_retention_check( class_exists( 'Alynt_Drime_Backups_Dashboard_Plugin' ) && class_exists( 'Alynt_Drime_Backups_Dashboard_Admin_Page' ), 'actual reorganized root loader available' );
	adb_retention_check( 3 === $queries, 'root/core preflight performs only owned absence reads' );
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
		$source = array( 'wp_options', 'wp_alynt_drime_dashboard_snapshots', 'wp_alynt_drime_dashboard_actions' )[ $index ];
		adb_retention_query( 'CREATE TABLE ' . $table . ' LIKE ' . $source );
		$created[]      = $table;
		$fixture_status = $exists( $table );
		adb_retention_check( 'InnoDB' === $fixture_status['Engine'], 'genuine owned InnoDB schema-only clone' );
	}
	$groups[] = 'exact ownership, genuine core and schema-only fixture clones';
	adb_retention_batch_cases();
	adb_retention_reporting_cases();
	$cleanup();
	adb_retention_check( microtime( true ) - $started < 40, 'whole proof bounded wall time' );
	echo json_encode(
		array(
			'checks'         => $checks,
			'groups'         => $groups,
			'queries'        => $queries,
			'cleaned'        => $cleaned,
			'tables_removed' => count( $created ),
			'seconds'        => round( microtime( true ) - $started, 3 ),
		)
	) . "\n";
} finally {
	$cleanup();
	$wpdb->close();
}
