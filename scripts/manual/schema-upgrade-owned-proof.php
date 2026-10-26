<?php
/**
 * Explicitly gated installed-core/database proof; never run by CI or application.
 * Run only after Site Operations target/fixture confirmation and mysql baseline.
 * MYSQL_PWD is inherited in-process, never an argument or recorded artifact.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.NotPrepared -- Explicit approved physical DDL/failure/cleanup proof: fresh validated identifier manifest, no application tables, no request/cache path. WP6.0 has no %i identifier support.
if ( PHP_SAPI !== 'cli' || count( $argv ) !== 3 || '--confirmed-plugin-tester-owned-proof' !== $argv[1] || ! preg_match( '/^adbpf_[a-f0-9]{12}_$/D', $argv[2] ) || ! getenv( 'MYSQL_PWD' ) ) {
	fwrite( STDERR, "Requires exact local target approval, fresh owned prefix and MYSQL_PWD\n" );
	exit( 1 );
}
define( 'ADB_SCHEMA_PROOF', true );
require __DIR__ . '/schema-proof-sql-scope.php';
require __DIR__ . '/schema-proof-core.php';
if ( false === $wpdb->query( 'SET SESSION max_statement_time=3, lock_wait_timeout=3, innodb_lock_wait_timeout=3' ) ) {
	throw new RuntimeException( 'Bounded local session could not be established' );
}
$prefix = $argv[2];
$wpdb->set_prefix( $prefix );
$tables          = array( $wpdb->options, Alynt_Drime_Backups_Dashboard_Storage::sites_table(), Alynt_Drime_Backups_Dashboard_Storage::snapshots_table(), Alynt_Drime_Backups_Dashboard_Storage::actions_table() );
$checks          = 0;
$groups          = array();
$armed           = false;
$cleaned         = false;
$fault_mode      = '';
$target          = '';
$queries         = array();
$ddl             = array();
$faults          = 0;
$physical_errors = 0;
/**
 * Counts a proof assertion without exposing SQL values.
 *
 * @param bool   $condition Observed condition.
 * @param string $label Fixed assertion description.
 * @return void
 * @throws RuntimeException When proof fails.
 */
function adb_schema_check( $condition, $label ) {
	global $checks;
	++$checks;
	if ( ! $condition ) {
		throw new RuntimeException( 'Assertion failed: ' . $label ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Fixed-label CLI exception, no HTML/request output.
	}
}
// Every target identifier must belong to this manifest. Reject application SQL.
$guard   = function ( $sql ) use ( $tables, &$queries, &$fault_mode, &$target, &$faults, &$physical_errors ) {
	global $wpdb;
	if ( $wpdb->last_error && false !== strpos( $wpdb->last_query, 'ADB_OWNED_FAULT' ) ) {
		++$physical_errors;
	}
	adb_schema_assert_sql_scope( $sql, $tables );
	$queries[] = $sql;
	if ( $fault_mode && preg_match( '/^' . preg_quote( $fault_mode, '/' ) . ' TABLE\s+`?' . preg_quote( $target, '/' ) . '`?\b/i', $sql ) ) {
		++$faults;
		return $fault_mode . ' TABLE ' . $target . ' ADB_OWNED_FAULT INVALID SQL';
	}
	return $sql;
};
$capture = function ( $sql ) use ( &$ddl ) {
	foreach ( $sql as $query ) {
		if ( preg_match( '/^CREATE TABLE (\S+)/', $query, $match ) ) {
			$ddl[ $match[1] ] = $query;
		}
	}
	return $sql;
};
$cleanup = function () use ( &$armed, &$cleaned, &$fault_mode, $tables ) {
	global $wpdb;
	if ( ! $armed || $cleaned ) {
		return;
	}
	$fault_mode = '';
	foreach ( array_reverse( $tables ) as $table ) {
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) );
		if ( $exists === $table ) {
			$wpdb->get_var( 'SELECT COUNT(*) FROM ' . $table ); // Owned cleanup preview.
			if ( false === $wpdb->query( 'DROP TABLE ' . $table ) ) {
				throw new RuntimeException( 'Owned table cleanup failed' );
			}
		}
		adb_schema_check( null === $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) ), 'owned table removed' );
	}
	$cleaned = true;
};
add_filter( 'query', $guard );
add_filter( 'dbdelta_queries', $capture );
register_shutdown_function(
	function () use ( $cleanup ) {
		try {
			$cleanup();
		} catch ( Throwable $error ) {
				fwrite( STDERR, "Owned cleanup incomplete; inspect exact manifest\n" ); }
	}
);
$schema       = Alynt_Drime_Backups_Dashboard_Storage::OPTION_SCHEMA_VERSION;
$marker       = function () use ( $schema ) {
	global $wpdb;
	// get_var maps an empty string to null; preserve a real empty retry marker.
	$row = $wpdb->get_row( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = %s", $schema ) );
	return is_object( $row ) ? $row->option_value : null;
};
$reset_marker = function ( $value ) use ( $schema, $marker ) {
	update_option( $schema, $value, false );
	wp_cache_flush();
	adb_schema_check( $value === $marker(), 'physical marker fixture reset' );
};
$success      = function () use ( $marker, &$ddl, $tables ) {
	global $wpdb;
	adb_schema_check( true === Alynt_Drime_Backups_Dashboard_Storage::maybe_upgrade(), 'genuine successful retry' );
	adb_schema_check( '8' === $marker(), 'physical completion marker' );
	foreach ( array_slice( $tables, 1 ) as $table ) {
		adb_schema_check( $table === $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) ), 'physical table presence' );
		adb_schema_check( array() === dbDelta( $ddl[ $table ], false ), 'actual core comparison converges' );
	}
};
try {
	foreach ( $tables as $table ) {
		adb_schema_check( null === $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) ), 'fresh manifest has no collisions' );
	}
	$armed = true;
	adb_schema_check( false !== $wpdb->query( "CREATE TABLE $wpdb->options (option_id bigint unsigned NOT NULL AUTO_INCREMENT, option_name varchar(191) NOT NULL, option_value longtext NOT NULL, autoload varchar(20) NOT NULL DEFAULT 'off', PRIMARY KEY (option_id), UNIQUE KEY option_name (option_name))" ), 'owned options table created' );
	$reset_marker( '7' );
	$success();
	$groups[] = 'genuine fresh install';
	foreach ( array_slice( $tables, 1 ) as $target ) {
		adb_schema_check( '0' === $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $target ), 'empty owned DROP preview' );
		adb_schema_check( false !== $wpdb->query( 'DROP TABLE ' . $target ), 'fixture table removed for CREATE failure' );
		$reset_marker( '7' );
		$fault_mode   = 'CREATE';
		$prior_errors = $physical_errors;
		$result       = Alynt_Drime_Backups_Dashboard_Storage::maybe_upgrade();
		$fault_mode   = '';
		adb_schema_check( is_wp_error( $result ), 'actual failed CREATE rejected' );
		adb_schema_check( '7' === $marker(), 'failed CREATE retains old physical version' );
		adb_schema_check( $physical_errors > $prior_errors, 'MariaDB CREATE error witnessed' );
		adb_schema_check( false === strpos( $result->get_error_message(), 'SQL' ), 'safe failure output' );
		$success();
		$groups[] = 'CREATE failure/retry ' . substr( $target, strlen( $prefix ) );
	}
	foreach ( array_combine( array_slice( $tables, 1 ), array( 'last_error_summary', 'payload_json', 'result_summary' ) ) as $target => $column ) {
		adb_schema_check( '0' === $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $target ), 'empty owned ALTER preview' );
		adb_schema_check( false !== $wpdb->query( 'ALTER TABLE ' . $target . ' DROP COLUMN ' . $column ), 'owned prior-schema fixture' );
		$reset_marker( '7' );
		$fault_mode   = 'ALTER';
		$prior_errors = $physical_errors;
		$result       = Alynt_Drime_Backups_Dashboard_Storage::maybe_upgrade();
		$fault_mode   = '';
		adb_schema_check( is_wp_error( $result ), 'actual failed ALTER rejected despite table presence' );
		adb_schema_check( '7' === $marker(), 'failed ALTER retains old physical version' );
		adb_schema_check( $physical_errors > $prior_errors, 'MariaDB ALTER error witnessed' );
		$success();
		$groups[] = 'ALTER failure/retry ' . substr( $target, strlen( $prefix ) );
	}
	// Reinstallation must invalidate a previously-current marker before failed DDL.
	$target = $tables[1];
	adb_schema_check( false !== $wpdb->query( 'ALTER TABLE ' . $target . ' DROP COLUMN last_error_summary' ), 'owned forced-reinstall fixture' );
	$fault_mode = 'ALTER';
	$result     = Alynt_Drime_Backups_Dashboard_Storage::maybe_install();
	$fault_mode = '';
	adb_schema_check( is_wp_error( $result ), 'failed forced reinstall rejected' );
	adb_schema_check( '' === $marker(), 'forced failure cannot retain current marker' );
	$success();
	$groups[]      = 'forced-current invalidation/retry';
	$prior_queries = count( $queries );
	adb_schema_check( true === Alynt_Drime_Backups_Dashboard_Storage::maybe_upgrade(), 'current-version fast path' );
	// Core can reload an empty alloptions cache in this non-autoload-only fixture.
	// The contract is no schema metadata/DDL or writes, not zero core option reads.
	$fast_queries = array_slice( $queries, $prior_queries );
	adb_schema_check( count( $fast_queries ) <= 3, 'bounded core option reads' );
	foreach ( $fast_queries as $sql ) {
		adb_schema_check( 0 === strpos( $sql, 'SELECT ' ) && false !== strpos( $sql, ' FROM ' . $wpdb->options ), 'current marker avoids schema queries and writes' );
	}
	$autoload = $wpdb->get_var( $wpdb->prepare( "SELECT autoload FROM $wpdb->options WHERE option_name = %s", $schema ) );
	adb_schema_check( in_array( $autoload, array( 'off', 'no', 'auto-off' ), true ), 'physical autoload disabled' );
	adb_schema_check( 7 === $faults, 'exactly seven bounded owned DDL failures' );
	$groups[] = 'no-op and autoload';
	$cleanup();
	echo json_encode(
		array(
			'checks'               => $checks,
			'groups'               => $groups,
			'wordpress'            => $wp_version,
			'php'                  => PHP_VERSION,
			'owned_tables_removed' => count( $tables ),
			'ddl_faults'           => $faults,
			'physical_errors'      => $physical_errors,
			'queries'              => count( $queries ),
			'prefix'               => $prefix,
		)
	) . "\n";
} catch ( Throwable $error ) {
	$fault_mode = '';
	try {
		$cleanup();
	} catch ( Throwable $cleanup_error ) {
		fwrite( STDERR, "Owned cleanup incomplete; inspect exact manifest\n" ); }
	fwrite(
		STDERR,
		json_encode(
			array(
				'checks'           => $checks,
				'completed_groups' => $groups,
				'prefix'           => $prefix,
				'ddl_faults'       => $faults,
				'cleanup'          => $cleaned,
			)
		) . "\n"
	);
	fwrite( STDERR, $error->getMessage() . "\n" );
	exit( 1 );
}
