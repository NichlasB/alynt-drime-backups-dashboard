<?php
/**
 * Explicit fixed Plugin Tester ADBC2 worker, no application entry or storage write.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( PHP_SAPI !== 'cli' || 6 !== count( $argv ) || '--confirmed-plugin-tester-connected-cleanup' !== $argv[1]
	|| ! preg_match( '/\Aadbb_[a-f0-9]{12}_\z/', $argv[2] ) || ! preg_match( '/\A[a-f0-9]{32}\z/', $argv[3] )
	|| ! preg_match( '/\A[a-f0-9]{64}\z/', $argv[4] ) || ! in_array( $argv[5], array( 'manifest', 'missing-table', 'positive' ), true ) || defined( 'ABSPATH' ) ) {
	exit( 1 );
}
// phpcs:disable WordPress.DB.RestrictedFunctions -- Connection-free ABI guard before the approved private owner proof.
if ( 'Windows' !== PHP_OS_FAMILY || '8.5.12' !== PHP_VERSION || ! extension_loaded( 'mysqli' ) || extension_loaded( 'pdo_mysql' )
	|| 'mysqlnd 8.5.12' !== mysqli_get_client_info() || ( 'manifest' !== $argv[5] && ! getenv( 'MYSQL_PWD' ) ) ) {
	exit( 1 );
}
// phpcs:enable WordPress.DB.RestrictedFunctions
$request = $argv;
$argv    = array( __FILE__, '--confirmed-plugin-tester-owned-bootstrap-proof', $request[2], 'audit' );
ob_start();
require __DIR__ . '/source-policy-bootstrap-owned-proof.php';
$audit = ob_get_clean();
if ( 1 !== preg_match( '/\Aaudit checks:[0-9]+ native_connections:0 sql_queries:0 fixtures:0\n\z/', $audit ) ) {
	exit( 1 );
}
require __DIR__ . '/source-policy-connected-cleanup-operation.php';

/** Full loaded producer/ABI and controller/bridge/model image, not authentication.
 *
 * @return string
 */
function adbc2_image() {
	$files = get_included_files();
	foreach ( array( PHP_BINARY, dirname( PHP_BINARY ) . '/php8.dll', ini_get( 'extension_dir' ) . '/php_mysqli.dll', __DIR__ . '/source-policy-connected-cleanup-controller.ps1', __DIR__ . '/source-policy-connected-cleanup-bridge.php', __DIR__ . '/source-policy-connected-cleanup-cohort.php', __DIR__ . '/source-policy-cleanup-process.ps1' ) as $file ) {
		$files[] = $file;
	}
	$files = array_unique( array_map( 'realpath', $files ) );
	sort( $files, SORT_STRING );
	$rows = array();
	foreach ( $files as $file ) {
		adbb_check( is_string( $file ) && is_file( $file ), 'finite connected source file' );
		$rows[] = str_replace( '\\', '/', $file ) . ':' . hash_file( 'sha256', $file );
	}
	return hash( 'sha256', implode( "\n", $rows ) );
}

$stage = 'manifest';
try {
	$source = adbc2_image();
	if ( 'manifest' === $request[5] ) {
		echo $source, "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed digest only.
		return;
	}
	adbb_check( hash_equals( $request[4], $source ), 'initial frozen connected image' );
	$stage             = 'native-case';
	$frame['password'] = getenv( 'MYSQL_PWD' );
	$wire              = adbc2_owner( $class, $frame, $binding, $request, $source );
	$frame['password'] = '';
	adbb_check( hash_equals( $source, adbc2_image() ), 'unchanged image after owned release' );
	$stage = 'hold';
	fwrite( STDOUT, $wire ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Strict bounded ADBC2 scalar DATA only.
	fflush( STDOUT );
	$token = fgets( STDIN, 256 );
	adbb_check( 'ADBC2-ACK|' . $request[3] . '|' . $source . '|' . $request[5] . '|' . getmypid() . "\n" === $token, 'exact connected release token' );
} catch ( Throwable $error ) {
	// No raw SQL, exception messages, paths, handles or secrets cross the error channel.
	fwrite( STDERR, 'ADBC2 failure stage:' . $stage . ' checks:' . $checks . "\n" ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed enums and integer count only.
	exit( 1 );
}
