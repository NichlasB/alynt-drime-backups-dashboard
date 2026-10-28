<?php
/**
 * Confirmed private Plugin Tester worker; never production or application bootstrap.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( PHP_SAPI !== 'cli' || 6 !== count( $argv ) || '--confirmed-plugin-tester-per-case-cleanup' !== $argv[1]
	|| ! preg_match( '/\Aadbb_[a-f0-9]{12}_\z/', $argv[2] ) || ! preg_match( '/\A[a-f0-9]{32}\z/', $argv[3] )
	|| ! preg_match( '/\A[a-f0-9]{64}\z/', $argv[4] ) || ! in_array( $argv[5], array( 'manifest', 'raw', 'default', 'wrong-source', 'all', 'incompatible_sql_modes', 'missing-database', 'missing-table', 'positive' ), true ) || defined( 'ABSPATH' ) ) {
	fwrite( STDERR, "Requires confirmed fixed Plugin Tester per-case cleanup scope\n" );
	exit( 1 );
}
// phpcs:disable WordPress.DB.RestrictedFunctions -- Read-only ABI check before any native connection.
if ( 'Windows' !== PHP_OS_FAMILY || '8.5.12' !== PHP_VERSION || ! extension_loaded( 'mysqli' ) || extension_loaded( 'pdo_mysql' )
	|| 'mysqlnd 8.5.12' !== mysqli_get_client_info() || ( 'manifest' !== $argv[5] && ! getenv( 'MYSQL_PWD' ) ) ) {
	fwrite( STDERR, "Requires audited bundled mysqli-only profile\n" );
	exit( 1 );
}
// phpcs:enable WordPress.DB.RestrictedFunctions
$request = $argv;
$argv    = array( __FILE__, '--confirmed-plugin-tester-owned-bootstrap-proof', $request[2], 'audit' );
ob_start(); // Existing pinned, connection-free audit emits only one finite fixed count line.
require __DIR__ . '/source-policy-bootstrap-owned-proof.php';
$audit = ob_get_clean();
if ( 1 !== preg_match( '/\Aaudit checks:[0-9]+ native_connections:0 sql_queries:0 fixtures:0\n\z/', $audit ) ) {
	exit( 1 );
}
require __DIR__ . '/source-policy-cleanup-evidence.php';
require __DIR__ . '/source-policy-cleanup-native-cases.php';
$stage = 'manifest';
try {
	$source = adbc_source();
	if ( 'manifest' === $request[5] ) {
		echo $source, "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed SHA256 only.
		return;
	}
	adbb_check( hash_equals( $request[4], $source ), 'frozen source image before native effects' );
	$stage             = 'native-case';
	$frame['password'] = getenv( 'MYSQL_PWD' );
	$wire              = 'raw' === $request[5] ? adbc_raw( $frame, $request, $source ) : adbc_owner( $class, $frame, $binding, $request, $source );
	$frame['password'] = '';
	adbb_check( hash_equals( $source, adbc_source() ), 'source image unchanged through owned cleanup' );
	$stage = 'hold';
	fwrite( STDOUT, $wire );
	fflush( STDOUT );
	// Bounded bytes. Parent owns the 25s deadline, closes this pipe on failure/exit,
	// and reconciles while this process is alive. No SQL or reopen after this point.
	$ack = fgets( STDIN, 128 );
	adbb_check( 'ADBC-ACK|' . $request[3] . '|' . $request[5] . '|' . getmypid() . "\n" === $ack, 'exact one-use private release token' );
} catch ( Throwable $error ) {
	$kind = in_array( get_class( $error ), array( 'RuntimeException', 'Error', 'mysqli_sql_exception' ), true ) ? get_class( $error ) : 'Throwable';
	fwrite( STDERR, 'native-cleanup failure stage:' . $stage . ' class:' . $kind . ' checks:' . $checks . ' assertion:' . ( $GLOBALS['adbb_last_check'] ?? 'none' ) . "\n" );
	exit( 1 );
}
