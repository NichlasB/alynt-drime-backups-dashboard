<?php
/**
 * Private socket-free DATA bridge; no native drivers, SQL, launcher or grants.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( PHP_SAPI !== 'cli' || 4 !== count( $argv ) || '--private-cleanup-data-bridge' !== $argv[1]
	|| ! preg_match( '/\A[a-f0-9]{32}\z/', $argv[2] ) || ! preg_match( '/\A[a-f0-9]{64}\z/', $argv[3] )
	|| defined( 'ABSPATH' ) || extension_loaded( 'mysqli' ) || extension_loaded( 'pdo_mysql' ) ) {
	exit( 1 );
}
define( 'ABSPATH', __DIR__ . '/' );
require __DIR__ . '/source-policy-cleanup-cohort.php';
$cohort = new Alynt_Drime_Backups_Dashboard_Cleanup_Cohort( $argv[2], $argv[3], 0 );
$line   = fgets( STDIN, 17000 );
while ( false !== $line ) {
	if ( strlen( $line ) > 16999 || "\n" !== substr( $line, -1 ) ) {
		exit( 1 );
	}
	$fields = explode( '|', substr( $line, 0, -1 ), 3 );
	if ( 3 !== count( $fields ) || ! preg_match( '/\A(?:0|[1-9][0-9]{0,4})\z/', $fields[1] ) ) {
		exit( 1 );
	}
	list( $command, $now, $data ) = $fields;
	$now                          = (int) $now;
	$result                       = false;
	if ( 'feed' === $command ) {
		$result = $cohort->feed( 'out', $data . "\n", $now );
	} elseif ( 'launch' === $command && preg_match( '/\A[1-9][0-9]{0,9}\z/', $data ) ) {
		$result = $cohort->launch( (int) $data, $now );
	} elseif ( in_array( $command, array( 'live', 'finish' ), true ) && preg_match( '/\A[1-9][0-9]{0,9},[01],0,0(?:,0)?\z/', $data ) ) {
		$items  = explode( ',', $data );
		$row    = array(
			'pid'          => (int) $items[0],
			'alive'        => '1' === $items[1],
			'scope'        => 'all-except-observer',
			'sessions'     => 0,
			'transactions' => 0,
		);
		$result = 'live' === $command && 4 === count( $items ) ? $cohort->live( $row, $now ) : ( 'finish' === $command && 5 === count( $items ) ? $cohort->finish( (int) $items[4], $row, $now ) : false );
	}
	if ( false === $result ) {
		exit( 1 );
	}
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Only fixed protocol DATA or a model-generated private ACK.
	echo is_string( $result ) ? $result : 'OK|' . $cohort->status()['phase'] . "\n";
	fflush( STDOUT );
	$line = fgets( STDIN, 17000 );
}
exit( 'done' === $cohort->status()['phase'] ? 0 : 1 );
