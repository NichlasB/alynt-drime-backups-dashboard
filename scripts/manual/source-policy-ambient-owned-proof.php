<?php
/**
 * Confirmed private core ambient/report-OFF proof; never application bootstrap.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.RestrictedFunctions, WordPress.WP.GlobalVariablesOverride.Prohibited -- Explicit owned private proof only.
if ( PHP_SAPI !== 'cli' || 6 !== count( $argv ) || '--confirmed-plugin-tester-owned-ambient' !== $argv[1]
	|| ! preg_match( '/\Aadbb_[a-f0-9]{12}_\z/', $argv[2] ) || ! preg_match( '/\A[a-f0-9]{32}\z/', $argv[3] )
	|| ! preg_match( '/\A[a-f0-9]{64}\z/', $argv[4] ) || ! in_array( $argv[5], array( 'audit', 'proof' ), true ) || defined( 'ABSPATH' ) ) {
	exit( 1 );
}
$ambient_request = $argv;
$argv            = array( __FILE__, '--confirmed-plugin-tester-owned-adapter', $argv[2], $argv[3], str_repeat( '0', 64 ), 'audit' );
ob_start();
require __DIR__ . '/source-policy-adapter-owned-proof.php';
$ambient_audit = ob_get_clean();
adbb_check( 1 === preg_match( '/\AADBA-AUDIT\|[a-f0-9]{64}\|[1-9][0-9]{0,4}\|3\n\z/', $ambient_audit ), 'unchanged strict connection-free prerequisite' );
$request = $ambient_request;
require __DIR__ . '/source-policy-ambient-proof-contract.php';
require __DIR__ . '/source-policy-ambient-proof-operation.php';
require __DIR__ . '/source-policy-ambient-proof-cases.php';
foreach ( array( 'db_connect', 'parse_db_host', 'select', 'set_sql_mode', 'set_charset' ) as $method ) {
	$definition = new ReflectionMethod( 'wpdb', $method );
	adbb_check( 'wpdb' === $definition->getDeclaringClass()->getName() && realpath( $binding['core_file'] ) === $definition->getFileName(), 'stock connector closure identity' );
}
adbb_check( '3' === ini_get( 'default_socket_timeout' ), 'bounded private ambient IO default' );
$files = array_merge( get_included_files(), array( PHP_BINARY, dirname( PHP_BINARY ) . '/php8.dll', ini_get( 'extension_dir' ) . '/php_mysqli.dll', __DIR__ . '/source-policy-ambient-proof-controller.ps1', __DIR__ . '/source-policy-cleanup-process.ps1' ) );
$files = array_unique( array_map( 'realpath', $files ) );
sort( $files, SORT_STRING );
$rows = array();
foreach ( $files as $file ) {
	adbb_check( is_string( $file ) && is_file( $file ), 'finite ambient source image' );
	$rows[] = str_replace( '\\', '/', $file ) . ':' . hash_file( 'sha256', $file );
}
$image = hash( 'sha256', implode( "\n", $rows ) );
if ( 'audit' === $request[5] ) {
	echo 'ADBO-AUDIT|', $image, '|', $checks, "|3\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Digest/count/initial mode only.
	return;
}
adbb_check( hash_equals( $request[4], $image ) && is_string( getenv( 'MYSQL_PWD' ) ) && '' !== getenv( 'MYSQL_PWD' ), 'frozen image and inherited private credential' );
$frame['password'] = getenv( 'MYSQL_PWD' );
$ambient           = null;
$owners            = array();
$groups            = 0;
$stage             = 'ambient';
$failed            = false;
$started           = hrtime( true );
try {
	// Constructor-equivalent input fields only; genuine connector(false) cannot load db-error.php/bail templates on failure.
	$ambient = ( new ReflectionClass( 'wpdb' ) )->newInstanceWithoutConstructor();
	foreach ( array(
		'dbuser'     => 'root',
		'dbpassword' => getenv( 'MYSQL_PWD' ),
		'dbname'     => 'local',
		'dbhost'     => '127.0.0.1:10004',
	) as $key => $value ) {
		adbb_bind( $ambient, $key, $value );
	}
	$wpdb = $ambient;
	adbb_check( true === $ambient->db_connect( false ) && true === $ambient->ready && $ambient->dbh instanceof mysqli, 'genuine stock core creates ambient session' );
	adbb_check( 0 === ( new mysqli_driver() )->report_mode && '1' === mysqli_get_client_stats()['connect_success'], 'core selected OFF and exactly one ambient session' );
	$ambient->options = $request[2] . 'options'; // Private clone only; never install the application's table prefix.
	$peer             = $ambient->dbh;
	$ambient_handle   = $peer;
	$ambient_id       = mysqli_thread_id( $peer );
	adba_peer( 'SET SESSION max_statement_time=3, innodb_lock_wait_timeout=1, lock_wait_timeout=1' );
	$before = mysqli_get_client_stats()['com_query'];
	adbb_check( is_string( $ambient->prepare( 'SELECT %d', 1 ) ) && mysqli_get_client_stats()['com_query'] === $before, 'genuine ambient placeholder warm-up without SQL' );
	$ambient_query = $GLOBALS['wp_filter']['query']->callbacks;
	adbb_check( 0 === has_filter( 'query', array( $ambient, 'remove_placeholder_escape' ) ) && 1 === count( $ambient_query[0] ), 'one preexisting exact ambient callback' );
	adb0_cases();
	adbb_check( ( hrtime( true ) - $started ) < 20000000000, 'original bounded ambient proof body' );
} catch ( Throwable $error ) {
	echo 'ADBO-FAIL1|', $stage, '|', $groups, '|', $checks, '|', hash( 'sha256', $GLOBALS['adbb_last_check'] ?? 'none' ), "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed stage/count/assertion digest; no native messages or credentials.
	$failed = true;
} finally {
	$cleanup_failed = false;
	foreach ( $owners as $owned ) {
		try {
			$cleanup_failed = true !== $owned->close() || $cleanup_failed;
			$owned->flush();
		} catch ( Throwable $cleanup_error ) {
			$cleanup_failed = true;
		}
	}
	if ( $ambient instanceof wpdb ) {
		try {
			$cleanup_failed = ( null !== $ambient->dbh && true !== $ambient->close() ) || $cleanup_failed;
			remove_filter( 'query', array( $ambient, 'remove_placeholder_escape' ), 0 ); // Our private ambient, only after owner cleanup checks.
			$ambient->flush();
		} catch ( Throwable $cleanup_error ) {
			$cleanup_failed = true;
		}
		adbb_bind( $ambient, 'dbpassword', '' );
	}
	$frame['password'] = '';
	$wpdb              = null;
	if ( $cleanup_failed ) {
		echo 'ADBO-FAIL1|cleanup|', $groups, '|', $checks, '|', hash( 'sha256', 'owned cleanup unconfirmed' ), "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed failure only.
		$failed = true;
	}
}
if ( $failed ) {
	exit( 1 );
}
foreach ( $files as $file ) {
	adbb_check( in_array( str_replace( '\\', '/', $file ) . ':' . hash_file( 'sha256', $file ), $rows, true ), 'ambient image unchanged through cleanup' );
}
$wire = 'ADBO1|' . $request[3] . '|' . $image . '|' . getmypid() . '|' . $checks . '|' . $groups . "|0|closed\n";
adbb_check( adb0_terminal( $wire, $request[3], $image, getmypid() ), 'bounded exact held-child terminal' );
echo $wire; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Strict validated receipt only.
fflush( STDOUT );
adbb_check( 'ADBO-ACK|' . $request[3] . '|' . $image . '|' . getmypid() . "\n" === fgets( STDIN, 256 ), 'exact parent release token' );
