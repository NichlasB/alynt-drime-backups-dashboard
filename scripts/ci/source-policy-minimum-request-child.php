<?php
/**
 * Private genuine request child. Refuses before core/auth/storage outside exact job.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.RestrictedFunctions -- Read-only mysqlnd counters for this exact private proof, not application SQL.
require_once __DIR__ . '/source-policy-minimum-request-contract.php';
if ( defined( 'ABSPATH' ) || ! adbg_entry( getenv() ) || ! adbg_identity( getenv() ) || PHP_SAPI !== 'cli' || '7.4.33' !== PHP_VERSION || 'Linux' !== PHP_OS_FAMILY || 'x86_64' !== php_uname( 'm' ) || PHP_ZTS || 8 !== PHP_INT_SIZE
	|| 3 !== count( $argv ) || '--owned-minimum-request-child' !== $argv[1] || ! preg_match( '/\A(?:0|[1-9][0-9]?)\z/', $argv[2] ) || (int) $argv[2] >= count( adbg_cases() )
	|| ! function_exists( 'posix_getppid' ) || 1 !== posix_getppid() || getmypid() <= 1 || ! extension_loaded( 'mysqli' ) || extension_loaded( 'pdo_mysql' ) ) {
	fwrite( STDERR, "Requires exact owned genuine request child\n" );
	exit( 1 );
}
define( 'ADB_M_REQUEST_PROOF', true );
require __DIR__ . '/source-policy-minimum-request-bootstrap.php';
require __DIR__ . '/source-policy-minimum-request-observer.php';
require __DIR__ . '/source-policy-minimum-request-auth.php';
require __DIR__ . '/source-policy-minimum-request-child-body.php';
