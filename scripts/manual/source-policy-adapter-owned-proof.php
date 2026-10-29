<?php
/**
 * Fixed dormant-adapter owned proof; no application bootstrap/provider/wiring.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.RestrictedFunctions -- Explicit fixed local owned fixture proof only.
if ( PHP_SAPI !== 'cli' || 6 !== count( $argv ) || '--confirmed-plugin-tester-owned-adapter' !== $argv[1]
	|| ! preg_match( '/\Aadbb_[a-f0-9]{12}_\z/', $argv[2] ) || ! preg_match( '/\A[a-f0-9]{32}\z/', $argv[3] )
	|| ! preg_match( '/\A[a-f0-9]{64}\z/', $argv[4] ) || ! in_array( $argv[5], array( 'audit', 'proof' ), true ) || defined( 'ABSPATH' ) ) {
	exit( 1 );
}
$request = $argv;
$argv    = array( __FILE__, '--confirmed-plugin-tester-owned-bootstrap-proof', $request[2], 'audit' );
ob_start();
require __DIR__ . '/source-policy-bootstrap-owned-proof.php';
$audit = ob_get_clean();
adbb_check( 1 === preg_match( '/\Aaudit checks:[0-9]+ native_connections:0 sql_queries:0 fixtures:0\n\z/', $audit ), 'base audit' );
$helper_pins = array(
	'formatting.php' => $pins['formatting.php'],
	'functions.php'  => $pins['functions.php'],
	'option.php'     => $pins['option.php'],
);
foreach ( $helper_pins as $file => $digest ) {
	adbb_check( hash_file( 'sha256', ABSPATH . WPINC . '/' . $file ) === strtolower( $digest ), 'pinned helper source' );
}
require_once ABSPATH . WPINC . '/formatting.php';
require_once ABSPATH . WPINC . '/functions.php'; // Its only direct include is the already pinned option.php.
foreach ( array( 'class-source-policy-save-contract.php', 'class-source-policy-stored-map.php', 'class-source-policy-native-adapter.php' ) as $file ) {
	require_once dirname( __DIR__, 2 ) . '/includes/' . $file;
}
require __DIR__ . '/source-policy-adapter-proof-cases.php';
$expected_core = array_map(
	static function ( $file ) {
		return realpath( ABSPATH . WPINC . '/' . $file );
	},
	array_merge( array_keys( $pins ), array_keys( $helper_pins ) )
);
foreach ( get_included_files() as $file ) {
	if ( 0 === strpos( $file, realpath( ABSPATH ) . DIRECTORY_SEPARATOR ) ) {
		adbb_check( in_array( $file, $expected_core, true ), 'only finite core library closure' );
	}
}
$driver = new mysqli_driver();
adbb_check( adbp_initial_mode( '7.1.3', PHP_VERSION, $driver->report_mode ), 'unchanged strict mysqli reporting profile' );
$fresh = mysqli_get_client_stats();
adbb_check( '0' === $fresh['connect_success'] && '0' === $fresh['connect_failure'] && '0' === $fresh['com_query'] && ! isset( $GLOBALS['wpdb'] ), 'helper audit has no session SQL or ambient handle' );
$files = array_merge( get_included_files(), array( PHP_BINARY, dirname( PHP_BINARY ) . '/' . $profile['runtime_module'], ini_get( 'extension_dir' ) . '/php_mysqli.dll', __DIR__ . '/source-policy-adapter-proof-controller.ps1', __DIR__ . '/source-policy-cleanup-process.ps1' ) );
$files = array_unique( array_map( 'realpath', $files ) );
sort( $files, SORT_STRING );
$rows = array();
foreach ( $files as $file ) {
	adbb_check( is_string( $file ) && is_file( $file ), 'finite adapter source image file' );
	$rows[] = str_replace( '\\', '/', $file ) . ':' . hash_file( 'sha256', $file );
}
$image = hash( 'sha256', implode( "\n", $rows ) );
if ( 'audit' === $request[5] ) {
	echo 'ADBA-AUDIT|', $image, '|', $checks, "|3\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed digest/count/profile only.
	return;
}
adbb_check( hash_equals( $request[4], $image ) && is_string( getenv( 'MYSQL_PWD' ) ) && '' !== getenv( 'MYSQL_PWD' ), 'frozen image and inherited private credential' );
$frame['password'] = getenv( 'MYSQL_PWD' );
$peer              = null;
$owners            = array();
$groups            = 0;
$stage             = 'peer';
$started           = hrtime( true );
$failed            = false;
try {
	$peer = mysqli_init();
	adbb_check( $peer instanceof mysqli, 'owned fixture peer allocation' );
	foreach ( array(
		MYSQLI_OPT_CONNECT_TIMEOUT => 2,
		MYSQLI_OPT_READ_TIMEOUT    => 3,
		MYSQLI_OPT_LOCAL_INFILE    => 0,
	) as $option => $value ) {
		adbb_check( mysqli_options( $peer, $option, $value ), 'bounded peer option' );
	}
	adbb_check( mysqli_real_connect( $peer, '127.0.0.1', 'root', getenv( 'MYSQL_PWD' ), 'local', 10004, null, 0 ), 'fixed owned fixture peer' );
	adba_peer( 'SET SESSION max_statement_time=3, innodb_lock_wait_timeout=1, lock_wait_timeout=1' );
	adba_cases();
	adbb_check( ( hrtime( true ) - $started ) < 20000000000, 'bounded whole adapter body' );
} catch ( Throwable $error ) {
	echo 'ADBA-FAIL1|', $stage, '|', $groups, '|', $checks, '|', hash( 'sha256', $GLOBALS['adbb_last_check'] ?? 'none' ), "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Source-owned stage/counts/assertion digest only, never native messages.
	$failed = true;
} finally {
	$cleanup_failed = false;
	foreach ( $owners as $owner ) {
		try {
			$cleanup_failed = true !== $owner->close() || $cleanup_failed;
			$owner->flush(); // Proof-owned memory cleanup; not claimed as adapter-internal flush.
		} catch ( Throwable $cleanup_error ) {
			$cleanup_failed = true;
		}
	}
	if ( $peer instanceof mysqli ) {
		try {
			$cleanup_failed = true !== mysqli_close( $peer ) || $cleanup_failed;
		} catch ( Throwable $cleanup_error ) {
			$cleanup_failed = true;
		}
	}
	if ( $cleanup_failed ) {
		echo 'ADBA-FAIL1|cleanup|', $groups, '|', $checks, '|', hash( 'sha256', 'owned cleanup unconfirmed' ), "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed cleanup failure/counts only.
		$failed = true;
	}
}
if ( $failed ) {
	exit( 1 );
}
foreach ( $files as $file ) {
	adbb_check( in_array( str_replace( '\\', '/', $file ) . ':' . hash_file( 'sha256', $file ), $rows, true ), 'image unchanged through adapter cleanup' );
}
echo 'ADBA1|', $request[3], '|', $image, '|', getmypid(), '|', $checks, '|', $groups, "|3|closed\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Strict finite receipt only.
fflush( STDOUT );
adbb_check( 'ADBA-ACK|' . $request[3] . '|' . $image . '|' . getmypid() . "\n" === fgets( STDIN, 256 ), 'exact held-child release token' );
