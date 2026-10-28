<?php
/**
 * One fixed owned Unix-socket identity read; no WordPress or adapter execution.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( PHP_SAPI !== 'cli' || 2 !== count( $argv ) || '--owned-identity-audit' !== $argv[1]
	|| 'Linux' !== PHP_OS_FAMILY || '8.5.12' !== PHP_VERSION || 'x86_64' !== php_uname( 'm' ) || 8 !== PHP_INT_SIZE || PHP_ZTS
	|| 'true' !== getenv( 'GITHUB_ACTIONS' ) || 'workflow_dispatch' !== getenv( 'GITHUB_EVENT_NAME' )
	|| 'NichlasB/alynt-drime-backups-dashboard' !== getenv( 'GITHUB_REPOSITORY' ) || 'refs/heads/master' !== getenv( 'GITHUB_REF' )
	|| ! preg_match( '/\A[a-f0-9]{40}\z/', (string) getenv( 'GITHUB_SHA' ) ) || getenv( 'GITHUB_SHA' ) !== getenv( 'EXPECTED_SHA' )
	|| ! preg_match( '/\A[0-9]+\z/', (string) getenv( 'GITHUB_RUN_ID' ) ) || ! preg_match( '/\A[0-9]+\z/', (string) getenv( 'GITHUB_RUN_ATTEMPT' ) )
	|| ! extension_loaded( 'mysqlnd' ) || ! extension_loaded( 'mysqli' ) ) {
	fwrite( STDERR, "Requires exact owned Linux empty-database identity audit\n" );
	exit( 1 );
}

// phpcs:disable WordPress.DB.RestrictedFunctions, WordPress.DB.DirectDatabaseQuery -- Explicitly approved empty owned CI database; one literal SELECT only, never application runtime or an imported handle.
$connection  = null;
$result      = null;
$row         = null;
$wire        = null;
$transport   = null;
$valid       = false;
$close_ack   = false;
$native_code = 0;
$step        = 'context';
// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler -- Scoped fixed diagnostic mapping; no warning is ignored or printed raw.
set_error_handler(
	static function () {
		throw new RuntimeException( 'Owned audit diagnostic' );
	}
);
try {
	if ( 1 !== count( get_included_files() ) || '' !== ini_get( 'auto_prepend_file' ) || '' !== ini_get( 'auto_append_file' )
		|| 'mysqlnd 8.5.12' !== mysqli_get_client_info() || 3 !== ( new mysqli_driver() )->report_mode ) {
		throw new RuntimeException( 'Private context unavailable' );
	}
	$temporary = getenv( 'RUNNER_TEMP' );
	$directory = $temporary . '/adb-linux-identity-' . getenv( 'GITHUB_RUN_ID' ) . '-' . getenv( 'GITHUB_RUN_ATTEMPT' ) . '/socket';
	$socket    = $directory . '/native.sock';
	if ( realpath( $temporary ) !== $temporary || realpath( $directory ) !== $directory || is_link( $directory )
		|| ( fileperms( $directory ) & 0777 ) !== 0700 || fileowner( $directory ) !== getmyuid()
		|| strlen( $socket ) > 96 || is_link( $socket ) || 'socket' !== filetype( $socket ) || fileowner( $socket ) !== getmyuid() ) {
		throw new RuntimeException( 'Owned socket unavailable' );
	}
	require __DIR__ . '/source-policy-linux-identity-contract.php';
	$before = mysqli_get_client_stats();
	foreach ( array( 'connect_success', 'connect_failure', 'com_query' ) as $key ) {
		if ( '0' !== ( $before[ $key ] ?? null ) ) {
			throw new RuntimeException( 'Fresh client unavailable' );
		}
	}
	$step       = 'connect';
	$connection = mysqli_init();
	if ( ! $connection instanceof mysqli || true !== mysqli_options( $connection, MYSQLI_OPT_CONNECT_TIMEOUT, 2 )
		|| true !== mysqli_options( $connection, MYSQLI_OPT_READ_TIMEOUT, 3 ) || true !== mysqli_options( $connection, MYSQLI_OPT_LOCAL_INFILE, 0 )
		|| true !== mysqli_real_connect( $connection, 'localhost', 'root', '', null, null, $socket, 0 ) ) {
		throw new RuntimeException( 'Owned connection refused' );
	}
	$step      = 'identity';
	$wire      = mysqli_get_server_info( $connection );
	$transport = mysqli_get_host_info( $connection );
	$result    = mysqli_query( $connection, "SELECT VERSION() AS version,@@version_comment AS comment,@@version_compile_machine AS machine,@@version_compile_os AS os,@@skip_networking AS no_tcp,@@read_only AS read_only,@@autocommit AS ac,@@in_transaction AS tx,@@socket AS socket,CONNECTION_ID() AS connection_id,(SELECT COUNT(*) FROM information_schema.SCHEMATA WHERE SCHEMA_NAME NOT IN ('mysql','information_schema','performance_schema','sys')) AS application_schemas,(SELECT COUNT(*) FROM information_schema.PROCESSLIST WHERE ID<>CONNECTION_ID()) AS other_sessions,(SELECT COUNT(*) FROM information_schema.INNODB_TRX) AS transactions,CURRENT_USER() AS principal" );
	if ( ! $result instanceof mysqli_result || 1 !== mysqli_num_rows( $result ) ) {
		throw new RuntimeException( 'Bounded identity unavailable' );
	}
	$row   = mysqli_fetch_assoc( $result );
	$after = mysqli_get_client_stats();
	$valid = Alynt_Drime_Backups_Dashboard_Linux_Identity_Contract::valid( $row, $wire, $transport )
		&& '1' === ( $after['connect_success'] ?? null ) && '0' === ( $after['connect_failure'] ?? null ) && '1' === ( $after['com_query'] ?? null )
		&& 3 === ( new mysqli_driver() )->report_mode;
} catch ( Throwable $error ) {
	$valid = false;
	if ( $error instanceof mysqli_sql_exception && is_int( $error->getCode() ) && $error->getCode() >= 0 && $error->getCode() <= 999999 ) {
		$native_code = $error->getCode();
	}
} finally {
	try {
		if ( $result instanceof mysqli_result ) {
			mysqli_free_result( $result );
		}
	} catch ( Throwable $error ) {
		$valid = false;
	}
	try {
		$close_ack = $connection instanceof mysqli && true === mysqli_close( $connection );
	} catch ( Throwable $error ) {
		$close_ack = false;
	}
	restore_error_handler();
}
if ( ! $valid || ! $close_ack ) {
	fwrite( STDERR, 'Owned identity audit refused at ' . $step . ' code:' . $native_code . "\n" ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed stage enum and bounded errno only, no SQL/native message.
	exit( 1 );
}
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Bounded public version/session metadata and fixed effect/release facts only, no credentials or application DATA.
echo json_encode(
	array(
		'wire_version'   => $wire,
		'transport'      => $transport,
		'identity'       => $row,
		'client_queries' => 1,
		'close_ack'      => true,
		'qualification'  => 'not-established',
	)
), "\n";
