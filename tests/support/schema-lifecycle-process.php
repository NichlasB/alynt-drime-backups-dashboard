<?php
/**
 * Bounded owned PHP worker runner; no WordPress or database process.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Runs one fixed isolated scenario and always reaps the owned child.
 *
 * @param string $scenario Fixture scenario.
 * @return array
 */
function alynt_dashboard_schema_fixture( $scenario ) {
	$pipes = array();
	$process = proc_open( array( PHP_BINARY, __DIR__ . '/schema-lifecycle-worker.php', $scenario ), array( 0 => array( 'pipe', 'r' ), 1 => array( 'pipe', 'w' ), 2 => array( 'pipe', 'w' ) ), $pipes );
	if ( ! is_resource( $process ) ) throw new RuntimeException( 'Could not start isolated PHP fixture' );
	$output = '';
	$errors = '';
	try {
		fclose( $pipes[0] );
		stream_set_blocking( $pipes[1], false );
		stream_set_blocking( $pipes[2], false );
		$deadline = microtime( true ) + 10;
		do {
			$output .= stream_get_contents( $pipes[1] );
			$errors .= stream_get_contents( $pipes[2] );
			$status = proc_get_status( $process );
			if ( ! $status['running'] ) break;
			if ( microtime( true ) > $deadline ) throw new RuntimeException( 'Isolated PHP fixture timed out' );
			usleep( 10000 );
		} while ( true );
		$output .= stream_get_contents( $pipes[1] );
		$errors .= stream_get_contents( $pipes[2] );
		if ( 0 !== $status['exitcode'] || '' !== $errors ) throw new RuntimeException( 'Isolated fixture failed: ' . $errors );
		$decoded = json_decode( $output, true );
		if ( ! is_array( $decoded ) ) throw new RuntimeException( 'Invalid fixture output: ' . $output );
		return $decoded;
	} finally {
		$status = proc_get_status( $process );
		if ( $status['running'] ) proc_terminate( $process );
		foreach ( array( 1, 2 ) as $index ) fclose( $pipes[ $index ] );
		proc_close( $process );
	}
}
