<?php
/**
 * Explicitly approved W/L private child only; no registered application entry.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// Definition-only admission comes before all core, configuration and native effects.
require_once __DIR__ . '/source-policy-wl-request-contract.php';
$profile = getenv( 'WL_REQUEST_PROFILE' );
if ( defined( 'ABSPATH' ) || ! adbwl_entry( getenv(), $argv ) || PHP_SAPI !== 'cli' || '8.5.12' !== PHP_VERSION || PHP_ZTS || 8 !== PHP_INT_SIZE
	|| ( 'w' === $profile ? ( 'Windows' !== PHP_OS_FAMILY || 'AMD64' !== php_uname( 'm' ) ) : ( 'Linux' !== PHP_OS_FAMILY || 'x86_64' !== php_uname( 'm' ) ) )
	|| ! extension_loaded( 'mysqli' ) || ! extension_loaded( 'mysqlnd' ) || extension_loaded( 'pdo_mysql' ) ) {
	fwrite( STDERR, "Requires exact owned W/L genuine request child\n" );
	exit( 1 );
}
define( 'ADB_WL_REQUEST_PROOF', true );
define( 'ADB_M_REQUEST_PROOF', true ); // Shared fixture/case definitions only, never M entry admission.
require __DIR__ . '/source-policy-wl-request-bootstrap.php';
require __DIR__ . '/source-policy-minimum-request-observer.php';
require __DIR__ . '/source-policy-minimum-request-auth.php';
require __DIR__ . '/source-policy-minimum-request-fixtures.php';
require __DIR__ . '/source-policy-wl-request-fixtures.php';
require __DIR__ . '/source-policy-minimum-request-child-body.php';
