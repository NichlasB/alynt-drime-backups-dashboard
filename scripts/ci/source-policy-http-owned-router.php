<?php
/**
 * Fixed CI-only router; no installed endpoint or production registration.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// phpcs:disable WordPress.DB.RestrictedFunctions, WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL, WordPress.Security.NonceVerification -- Raw transport fixture; unchanged guarded entry owns capability and nonce checks.
// phpcs:disable WordPress.Security.ValidatedSanitizedInput -- Before core bootstrap: exact raw header/route validation by adbh_route must not normalize malformed transport bytes.
if ( 'cli-server' !== PHP_SAPI || 'true' !== getenv( 'ADB_HTTP_OWNED_APPROVED' ) ) {
	exit( 1 ); }
require __DIR__ . '/source-policy-http-owned-worker.php';
require __DIR__ . '/source-policy-http-bridge.php';
$root     = dirname( __DIR__, 3 );
$private  = require $root . '/private.php';
$exchange = $_SERVER['HTTP_X_ADB_EXCHANGE'] ?? '';
if ( ! is_string( $exchange ) || ! preg_match( '/\A[1-9][0-9]?\z/', $exchange ) || (int) $exchange > 64 ) {
	http_response_code( 400 );
	exit; }
$raw          = file_get_contents( 'php://input', false, null, 0, 16385 );
$headers      = getallheaders();
$header_bytes = 2;
foreach ( $headers as $key => $value ) {
	$header_bytes += strlen( $key ) + 2 + strlen( $value ) + 2; }
$meta = array(
	'host'              => $_SERVER['HTTP_HOST'] ?? null,
	'method'            => $_SERVER['REQUEST_METHOD'] ?? null,
	'target'            => $_SERVER['REQUEST_URI'] ?? null,
	'challenge'         => $_SERVER['HTTP_X_ADB_CHALLENGE'] ?? null,
	'header_bytes'      => $header_bytes,
	'body_bytes'        => is_string( $raw ) ? strlen( $raw ) : -1,
	'content_length'    => $_SERVER['CONTENT_LENGTH'] ?? null,
	'transfer_encoding' => $_SERVER['HTTP_TRANSFER_ENCODING'] ?? null,
	'content_type'      => $_SERVER['CONTENT_TYPE'] ?? null,
);
$kind = adbh_route( $meta, $private['challenge'] );
if ( false === $kind ) {
	http_response_code( 400 );
	exit; }
$GLOBALS['adbh_route']        = $kind;
$GLOBALS['adbh_entry_counts'] = array(
	'records'  => 0,
	'connects' => 0,
	'commands' => 0,
);
try {
	ob_start();
	adbh_bootstrap( 'request' );
	// Core registered shutdown_action_hook during bootstrap; register after it so
	// sidecar completion reflects real stock shutdown, never an early response.
	register_shutdown_function(
		static function () use ( $root, $exchange, $meta ) {
			try {
				$containment = adbh_finish_packet();
				adbh_check( ! error_get_last() || ! in_array( error_get_last()['type'], array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR ), true ), 'shutdown-fatal' );
				$packet = array(
					'run'         => $GLOBALS['adbh_private']['run'],
					'head'        => $GLOBALS['adbh_private']['head'],
					'image'       => $GLOBALS['adbh_private']['image'],
					'exchange'    => (int) $exchange,
					'meta'        => $meta,
					'bootstrap'   => adbh_ledger_packet( 'bootstrap' ),
					'auth'        => adbh_ledger_packet( 'auth' ),
					'counts'      => $GLOBALS['adbh_entry_counts'],
					'containment' => $containment,
					'checks'      => $GLOBALS['adbh_checks'],
				);
				$bytes  = json_encode( $packet );
				adbh_check( is_string( $bytes ) && strlen( $bytes ) <= 8192, 'receipt-bytes' );
				$file = $root . '/receipts/' . $exchange . '.json';
				adbh_check( ! file_exists( $file ), 'receipt-absence' );
				adbh_check( strlen( $bytes ) === file_put_contents( $file, $bytes, LOCK_EX ), 'receipt-write' );
			} catch ( Throwable $error ) {
				http_response_code( 503 ); }
		}
	);
	if ( in_array( $kind, array( 'login-form', 'login-submit' ), true ) ) {
		require ABSPATH . 'wp-login.php';
		exit;
	}
	$proof = dirname( __DIR__, 2 );
	foreach ( array( 'source-policy', 'source-policy-native-profile', 'source-policy-query-context', 'source-policy-no-reconnect-connection', 'source-policy-save-contract', 'source-policy-stored-map', 'source-policy-native-adapter', 'source-policy-guarded-entry', 'source-policy-lazy-provider', 'storage' ) as $name ) {
		require_once $proof . '/includes/class-' . $name . '.php'; }
	foreach ( array( 'reads', 'local-state-writes', 'writes', 'runtime-writes' ) as $name ) {
		require_once $proof . '/includes/traits/trait-site-repository-' . $name . '.php'; }
	require_once $proof . '/includes/class-site-repository.php';
	adbh_check( false === wp_cache_get( 'alynt_drime_backups_dashboard_source_policies', 'options' ), 'no-protected-cache' );
	$all = wp_cache_get( 'alloptions', 'options' );
	adbh_check( ! is_array( $all ) || ! array_key_exists( 'alynt_drime_backups_dashboard_source_policies', $all ), 'no-protected-autoload' );
	adbh_check( remove_filter( 'query', 'adbh_owned_observe', PHP_INT_MIN ) && false === has_filter( 'query', 'adbh_owned_observe' ), 'exact-observer-removal' );
	$before                       = mysqli_get_client_stats();
	$queries                      = $GLOBALS['wpdb']->num_queries;
	$result                       = adbh_bridge( $meta, $_POST, $private['challenge'] );
	$after                        = mysqli_get_client_stats();
	$GLOBALS['adbh_entry_counts'] = array(
		'records'  => $GLOBALS['wpdb']->num_queries - $queries,
		'connects' => (int) $after['connect_success'] - (int) $before['connect_success'],
		'commands' => (int) $after['com_query'] - (int) $before['com_query'],
	);
	adbh_check( (int) $after['connect_failure'] === (int) $before['connect_failure'] && 0 === ( new mysqli_driver() )->report_mode, 'no-reconnect-mode-drift' );
	add_filter( 'query', 'adbh_owned_observe', PHP_INT_MIN, 1 );
	header( 'Content-Type: application/json' );
	http_response_code( $result['status'] );
	echo json_encode( $result ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Private fixed JSON result projection; never diagnostics or handles.
} catch ( Throwable $error ) {
	$GLOBALS['adbh_failure'] = $GLOBALS['adbh_failure'] ?? str_repeat( '0', 64 );
	http_response_code( 503 );
}
