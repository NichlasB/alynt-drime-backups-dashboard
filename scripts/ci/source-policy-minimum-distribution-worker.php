<?php
/**
 * Default-off fixed entry into the existing genuine-core worker, not plugin wiring.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( defined( 'ABSPATH' ) ) {
	exit( 1 );
}
require_once __DIR__ . '/source-policy-minimum-distribution-contract.php';
if ( defined( 'ADB_MINIMUM_DISTRIBUTION' ) || ! adbm_distribution_entry( getenv() ) ) {
	fwrite( STDERR, "Requires exact separately approved distribution job\n" );
	exit( 1 );
}
define( 'ADB_MINIMUM_DISTRIBUTION', true );
if ( in_array( getenv( 'GENUINE_REQUEST_APPROVED' ), array( 'true' ), true ) || 'genuine-session-request-v1' === getenv( 'MINIMUM_OPERATION' ) ) {
	require __DIR__ . '/source-policy-minimum-request-worker.php';
	return;
}
if ( ! in_array( getenv( 'GENUINE_REQUEST_APPROVED' ), array( false, 'false' ), true ) ) {
	fwrite( STDERR, "Requires exact request proof selection\n" );
	exit( 1 );
}
require __DIR__ . '/source-policy-minimum-qualification.php';
