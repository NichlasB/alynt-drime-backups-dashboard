<?php
/**
 * Fixed approved Plugin Tester private raw reference, no WordPress or SQL.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( PHP_SAPI !== 'cli' || 5 !== count( $argv ) || '--confirmed-plugin-tester-raw-connected' !== $argv[1]
	|| ! preg_match( '/\A[a-f0-9]{32}\z/', $argv[2] ) || ! preg_match( '/\A[a-f0-9]{64}\z/', $argv[3] )
	|| ! in_array( $argv[4], array( 'manifest', 'proof' ), true ) || defined( 'ABSPATH' ) ) {
	fwrite( STDERR, "Requires confirmed fixed raw connected scope\n" );
	exit( 1 );
}
// phpcs:disable WordPress.DB.RestrictedFunctions -- Connection-free native ABI guard before any private reference.
if ( 'Windows' !== PHP_OS_FAMILY || '8.5.12' !== PHP_VERSION || ! extension_loaded( 'mysqli' ) || extension_loaded( 'pdo_mysql' )
	|| 'mysqlnd 8.5.12' !== mysqli_get_client_info() || ( 'proof' === $argv[4] && ! getenv( 'MYSQL_PWD' ) ) ) {
	fwrite( STDERR, "Requires fixed bundled mysqli-only profile\n" );
	exit( 1 );
}
// phpcs:enable WordPress.DB.RestrictedFunctions
define( 'ADCR_PRIVATE', true );
require __DIR__ . '/source-policy-connected-raw-operation.php';

/** Computes loaded-source/ABI correlation, not distribution authentication.
 *
 * @return string SHA256.
 */
function adcr_image() {
	$files = array_merge( get_included_files(), array( PHP_BINARY, dirname( PHP_BINARY ) . '/php8.dll', ini_get( 'extension_dir' ) . '/php_mysqli.dll', __DIR__ . '/source-policy-connected-raw-controller.ps1', __DIR__ . '/source-policy-connected-raw-bridge.php', __DIR__ . '/source-policy-cleanup-process.ps1' ) );
	$files = array_unique( array_map( 'realpath', $files ) );
	sort( $files, SORT_STRING );
	$rows = array();
	foreach ( $files as $file ) {
		adcr_check( is_string( $file ) && is_file( $file ), 'source-file' );
		$rows[] = str_replace( '\\', '/', $file ) . ':' . hash_file( 'sha256', $file );
	}
	return hash( 'sha256', implode( "\n", $rows ) );
}

set_error_handler( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler -- Scoped fixed error, never raw warnings or suppression.
	static function () {
		throw new RuntimeException( 'Raw diagnostic warning' );
	}
);
try {
	$source = adcr_image();
	if ( 'manifest' === $argv[4] ) {
		echo $source, "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed SHA256 only.
	} else {
		adcr_check( hash_equals( $argv[3], $source ), 'initial-source' );
		adcr_raw( $argv[2], $source );
	}
} catch ( Throwable $error ) {
	$primary = $GLOBALS['adcr_primary_kind'] ?? 'unconfirmed';
	$cleanup = $GLOBALS['adcr_cleanup_kind'] ?? 'not_attempted';
	fwrite( STDERR, 'Raw connected diagnostic unconfirmed primary:' . $primary . ' cleanup:' . $cleanup . "\n" ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed private enum values only, never exception messages/SQL/native objects/traces.
	exit( 1 );
} finally {
	restore_error_handler();
}
