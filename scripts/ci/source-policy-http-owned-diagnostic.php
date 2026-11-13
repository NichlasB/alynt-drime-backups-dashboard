<?php
/**
 * Pure bounded failure DATA, never raw messages, paths, SQL or runtime authority.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/** Classify source-owned location DATA without reading a path.
 *
 * @param mixed  $file Engine source filename, not exported.
 * @param mixed  $line Engine line.
 * @param string $root Fixed owned stage.
 * @return array
 */
function adbh_diagnostic_location( $file, $line, $root ) {
	$origin = 'other';
	if ( is_string( $file ) && strlen( $file ) <= 4096 ) {
		foreach ( array( 'core', 'proof' ) as $candidate ) {
			if ( 0 === strpos( $file, $root . '/' . $candidate . '/' ) ) {
				$origin = $candidate; }
		}
	}
	return array(
		'origin' => $origin,
		'line'   => is_int( $line ) && $line > 0 && $line <= 100000 ? $line : 0,
	);
}

/** Fixed diagnostic packet; supplied exception/message content never exported.
 *
 * @param Throwable $error Caught engine/core failure.
 * @param string    $root Fixed owned stage.
 * @return array
 */
function adbh_setup_diagnostic( Throwable $error, $root ) {
	$class     = get_class( $error );
	$stage     = $GLOBALS['adbh_stage'] ?? 'entry';
	$location  = $GLOBALS['adbh_diagnostic_location'] ?? adbh_diagnostic_location( $error->getFile(), $error->getLine(), $root );
	$assertion = $GLOBALS['adbh_failure'] ?? '';
	return array(
		'event'     => 'owned-setup-refused',
		'stage'     => in_array( $stage, array( 'entry', 'early-hooks', 'core-bootstrap', 'core-ready', 'install', 'seed', 'finalize' ), true ) ? $stage : 'entry',
		'exception' => in_array( $class, array( 'RuntimeException', 'Error', 'TypeError', 'ValueError', 'ParseError' ), true ) ? $class : 'other',
		'origin'    => $location['origin'],
		'line'      => $location['line'],
		'assertion' => is_string( $assertion ) && preg_match( '/\A[a-f0-9]{64}\z/', $assertion ) ? $assertion : null,
	);
}
