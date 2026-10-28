<?php
/**
 * Private socket-free bridge for raw diagnostic observations, never native grants.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( PHP_SAPI !== 'cli' || 5 !== count( $argv ) || '--private-raw-connected-data' !== $argv[1]
	|| ! preg_match( '/\A[a-f0-9]{32}\z/', $argv[2] ) || ! preg_match( '/\A[a-f0-9]{64}\z/', $argv[3] )
	|| ! preg_match( '/\A[1-9][0-9]{0,9}\z/', $argv[4] ) || defined( 'ABSPATH' ) || extension_loaded( 'mysqli' ) || extension_loaded( 'pdo_mysql' ) ) {
	exit( 1 );
}
define( 'ADCR_PRIVATE', true );
require __DIR__ . '/source-policy-connected-raw-record.php';
$record = new Alynt_Drime_Backups_Dashboard_Raw_Connected_Record( $argv[2], $argv[3], (int) $argv[4] );
$line   = fgets( STDIN, 17000 );
while ( false !== $line ) {
	if ( strlen( $line ) > 16999 || "\n" !== substr( $line, -1 ) ) {
		exit( 1 );
	}
	$parts = explode( '|', substr( $line, 0, -1 ), 3 );
	if ( 3 !== count( $parts ) || ! preg_match( '/\A(?:0|[1-9][0-9]{0,4})\z/', $parts[1] ) ) {
		exit( 1 );
	}
	list( $command, $now, $data ) = $parts;
	$result                       = false;
	if ( 'feed' === $command ) {
		$result = $record->feed( $data . "\n", (int) $now );
	} elseif ( 'live' === $command && preg_match( '/\A[1-9][0-9]{0,9},1,[01],0\z/', $data ) ) {
		$values = array_map( 'intval', explode( ',', $data ) );
		$result = $record->observe( $values[0], true, $values[2], $values[3], (int) $now );
	} elseif ( 'finish' === $command && preg_match( '/\A0,[1-9][0-9]{0,9},0,0,0\z/', $data ) ) {
		$values = array_map( 'intval', explode( ',', $data ) );
		$result = $record->finish( 0, $values[1], false, 0, 0, (int) $now );
	}
	if ( false === $result ) {
		exit( 1 );
	}
	echo is_string( $result ) ? $result : 'OK|' . $record->phase() . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed model-generated scalar protocol.
	fflush( STDOUT );
	$line = fgets( STDIN, 17000 );
}
exit( 'done' === $record->phase() ? 0 : 1 );
