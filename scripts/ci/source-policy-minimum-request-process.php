<?php
/**
 * Fixed fresh-request child supervision inside the same existing contained worker.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ADB_M_REQUEST_PROOF' ) ) {
	exit( 1 );
}

/**
 * Validate a child terminal, not a native receipt transferable outside its parent.
 *
 * @param string $wire Private child output.
 * @param int    $index Expected fixed case index.
 * @return array|false Aggregate count fields only.
 */
function adbg_child_terminal( $wire, $index ) {
	if ( ! is_string( $wire ) || strlen( $wire ) > 256 || ! is_int( $index ) || $index < 0 || $index >= count( adbg_cases() ) ) {
		return false;
	}
	if ( 1 !== preg_match( '/\AADBMGC1\|' . $index . '\|([1-9][0-9]{0,4})\|([0-9]{1,3})\|([0-9]{1,3})\|([0-9]{1,3})\|([0-9]{1,3})\|([0-9]{1,4})\|closed\n\z/', $wire, $match ) ) {
		return false;
	}
	return array_map( 'intval', array_slice( $match, 1 ) );
}

/**
 * One owned child only, bounded under the unchanged twenty-second body deadline.
 *
 * @param int $index Fixed case index.
 * @param int $started Whole proof start, never reset per child.
 * @return array Validated private count fields, never a handle.
 */
function adbg_child( $index, $started ) {
	$args = array( PHP_BINARY, '-n', '-d', 'extension=/usr/local/lib/php/extensions/no-debug-non-zts-20190902/mysqli.so', '-d', 'memory_limit=64M', '-d', 'mysqli.allow_persistent=0', '-d', 'default_socket_timeout=3', '-d', 'mysqlnd.net_read_timeout=3', __DIR__ . '/source-policy-minimum-request-child.php', '--owned-minimum-request-child', (string) $index );
	// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_proc_open -- Fixed owned same-image child and bounded private pipes; no shell or caller command.
	$process = proc_open(
		$args,
		array(
			0 => array( 'pipe', 'r' ),
			1 => array( 'pipe', 'w' ),
			2 => array( 'pipe', 'w' ),
		),
		$pipes,
		dirname( __DIR__, 2 )
	);
	adbg_check( is_resource( $process ), 'owned child started' );
	$out      = '';
	$err      = '';
	$accepted = false;
	$exit     = null;
	stream_set_blocking( $pipes[1], false );
	stream_set_blocking( $pipes[2], false );
	try {
		while ( true ) {
			adbg_check( hrtime( true ) - $started < 20000000000, 'unchanged whole-body deadline' );
			$out .= stream_get_contents( $pipes[1], 257 - strlen( $out ) );
			$err .= stream_get_contents( $pipes[2], 257 - strlen( $err ) );
			if ( adbg_failure_valid( $err ) && 0 === strpos( $err, 'ADBMGRF1|child|' . $index . '|' ) ) {
				$GLOBALS['adbg_failure_wire'] = $err;
				adbg_check( false, 'child bounded failure before cleanup' );
			}
			adbg_check( strlen( $out ) <= 256 && strlen( $err ) <= 256 && '' === $err, 'bounded private child output without raw diagnostics' );
			$status = proc_get_status( $process );
			if ( ! $accepted && false !== strpos( $out, "\n" ) ) {
				$counts = adbg_child_terminal( $out, $index );
				adbg_check( $status['running'] && false !== $counts, 'held exact child terminal' );
				adbg_check( '0' === $GLOBALS['wpdb']->get_var( 'SELECT COUNT(*) FROM information_schema.PROCESSLIST WHERE ID<>CONNECTION_ID()' ), 'no other owned-account sessions while child held' );
				adbg_check( strlen( "ACK\n" ) === fwrite( $pipes[0], "ACK\n" ), 'owned child acknowledgement' );
				fclose( $pipes[0] );
				$accepted = true;
			}
			if ( ! $status['running'] ) {
				$exit = $status['exitcode'];
				break;
			}
			usleep( 1000 );
		}
		$out .= stream_get_contents( $pipes[1], 257 - strlen( $out ) );
		$err .= stream_get_contents( $pipes[2], 257 - strlen( $err ) );
		adbg_check( $accepted && 0 === $exit && '' === $err && false !== adbg_child_terminal( $out, $index ), 'child closed without trailing output' );
	} finally {
		$status = proc_get_status( $process );
		if ( $status['running'] ) {
			proc_terminate( $process );
			$until = hrtime( true ) + 1000000000;
			while ( proc_get_status( $process )['running'] && hrtime( true ) < $until - 500000000 ) {
				usleep( 1000 );
			}
			if ( proc_get_status( $process )['running'] ) {
				proc_terminate( $process, 9 );
				while ( proc_get_status( $process )['running'] && hrtime( true ) < $until ) {
					usleep( 1000 );
				}
			}
		}
		$released = ! proc_get_status( $process )['running'];
		foreach ( $pipes as $pipe ) {
			if ( is_resource( $pipe ) ) {
				fclose( $pipe );
			}
		}
		// Never block in proc_close on an unobserved release; outer container deadline remains authoritative.
		adbg_check( $released, 'owned child exit observed before reap' );
		proc_close( $process );
	}
	return $counts;
}
