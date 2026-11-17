<?php
/**
 * Private first-failure DATA only; never a receipt, response or authority grant.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

// Shared source-label definitions only; this never loads core or starts a worker.
require_once __DIR__ . '/source-policy-http-owned-diagnostic.php';

/** Project only fixed failure fields; do not inspect messages or arguments.
 *
 * @param Throwable|null $error Caught failure, null for early transport refusal.
 * @param mixed          $exchange Validated owned exchange number, otherwise zero.
 * @param string         $root Fixed owned stage.
 * @return array
 */
function adbh_request_diagnostic( ?Throwable $error, $exchange, $root ) {
	$stage    = $GLOBALS['adbh_stage'] ?? '';
	$class    = null === $error ? 'unavailable' : get_class( $error );
	$location = null === $error ? array() : ( $GLOBALS['adbh_diagnostic_location'] ?? adbh_diagnostic_location( $error->getFile(), $error->getLine(), $root ) );
	$location = is_array( $location ) ? $location : array();
	$origin   = $location['origin'] ?? '';
	$line     = $location['line'] ?? 0;
	$label    = $GLOBALS['adbh_failure'] ?? '';
	return array(
		'event'     => 'owned-request-refused',
		'exchange'  => is_int( $exchange ) && $exchange >= 1 && $exchange <= 64 ? $exchange : 0,
		'stage'     => in_array( $stage, array( 'exchange', 'route', 'early-hooks', 'core-bootstrap', 'core-ready', 'login', 'bridge', 'shutdown', 'receipt' ), true ) ? $stage : 'unavailable',
		'exception' => in_array( $class, array( 'RuntimeException', 'Error', 'TypeError', 'ValueError', 'ParseError', 'unavailable' ), true ) ? $class : 'other',
		'origin'    => in_array( $origin, array( 'core', 'proof' ), true ) ? $origin : 'other',
		'line'      => is_int( $line ) && $line > 0 && $line <= 100000 ? $line : 0,
		'assertion' => is_string( $label ) && preg_match( '/\A[a-f0-9]{64}\z/', $label ) ? $label : null,
	);
}

/** Emit at most one bounded private frame; never replace the original failure.
 *
 * @param Throwable|null $error Caught failure, null for early refusal.
 * @param mixed          $exchange Validated owned exchange, otherwise zero.
 * @param string         $root Fixed owned stage.
 * @return bool Whether a complete diagnostic frame was written.
 */
function adbh_request_report( ?Throwable $error, $exchange, $root ) {
	if ( ! empty( $GLOBALS['adbh_request_reported'] ) ) {
		return false; }
	$GLOBALS['adbh_request_reported'] = true;
	try {
		$json = json_encode( adbh_request_diagnostic( $error, $exchange, $root ) );
		if ( ! is_string( $json ) ) {
			return false; }
		$frame = 'ADBH_REQUEST_FAILURE ' . $json . "\n";
		if ( strlen( $frame ) > 1024 ) {
			return false; }
		return strlen( $frame ) === file_put_contents( 'php://stderr', $frame );
	} catch ( Throwable $diagnostic_error ) {
		// Missing diagnostics stay unavailable; never mask a refusal or create success.
		return false;
	}
}
