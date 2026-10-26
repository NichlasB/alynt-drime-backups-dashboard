<?php
/**
 * Bounded owned child-process coordination; no pipes, services or external PIDs.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

/**
 * Waits for an owned marker with an explicit wall-time bound.
 *
 * @param string $path Exact marker path.
 * @param float  $seconds Deadline budget.
 * @return void
 * @throws RuntimeException On expiry or malformed output.
 */
function adb_poll_wait_marker( $path, $seconds ) {
	$deadline = microtime( true ) + $seconds;
	do {
		clearstatcache( true, $path );
		if ( is_file( $path ) && filesize( $path ) > 0 ) {
			if ( filesize( $path ) > 4096 ) {
				throw new RuntimeException( 'Oversized owned marker' );
			}
			return;
		}
		usleep( 10000 );
	} while ( microtime( true ) < $deadline );
	throw new RuntimeException( 'Owned marker deadline expired' );
}

/**
 * Reads a bounded marker under the writer's lock, never a partial JSON document.
 *
 * @param string $path Exact owned marker.
 * @return string
 * @throws RuntimeException On failed or oversized read.
 */
function adb_poll_read_marker( $path ) {
	$stream = fopen( $path, 'rb' );
	if ( ! $stream || ! flock( $stream, LOCK_SH ) ) {
		throw new RuntimeException( 'Owned marker read lock failed' );
	}
	try {
		$content = stream_get_contents( $stream, 4097 );
		if ( false === $content || strlen( $content ) > 4096 ) {
			throw new RuntimeException( 'Owned marker read failed' );
		}
		return $content;
	} finally {
		flock( $stream, LOCK_UN );
		fclose( $stream );
	}
}

/**
 * Joins or terminates only the supplied owned process and removes its exact files.
 *
 * @param array $child Owned resource/manifest.
 * @return void
 * @throws RuntimeException On retained process or files.
 */
function adb_poll_reap( array $child ) {
	if ( is_resource( $child['process'] ) ) {
		$status = proc_get_status( $child['process'] );
		if ( $status['running'] ) {
			proc_terminate( $child['process'] );
			$deadline = microtime( true ) + 3;
			do {
				usleep( 10000 );
				$status = proc_get_status( $child['process'] );
			} while ( $status['running'] && microtime( true ) < $deadline );
			if ( $status['running'] ) {
				throw new RuntimeException( 'Owned child remains running' );
			}
		}
		proc_close( $child['process'] );
	}
	foreach ( array( 'ready.json', 'go.txt', 'result.json', 'stage.json', 'stdout.log', 'stderr.log' ) as $name ) {
		$path = $child['directory'] . '/' . $name;
		if ( file_exists( $path ) && ! unlink( $path ) ) {
			throw new RuntimeException( 'Owned marker cleanup failed' );
		}
	}
	if ( ! rmdir( $child['directory'] ) ) {
		throw new RuntimeException( 'Owned process directory cleanup failed' );
	}
}

/**
 * Starts the same fixed proof entry on an owned directory and prefix.
 *
 * @param string $prefix Owned table prefix.
 * @param string $action Fixed worker action.
 * @return array
 * @throws RuntimeException If process setup fails.
 */
function adb_poll_spawn( $prefix, $action ) {
	$directory = sys_get_temp_dir() . '/' . $prefix . bin2hex( random_bytes( 6 ) );
	if ( file_exists( $directory ) || ! mkdir( $directory ) ) {
		throw new RuntimeException( 'Owned child directory collision' );
	}
	$command = array( PHP_BINARY, '-n', '-d', 'extension_dir=' . ini_get( 'extension_dir' ), '-d', 'extension=mysqli', '-d', 'extension=mbstring', __DIR__ . '/poll-completion-owned-proof.php', '--confirmed-plugin-tester-owned-proof', $prefix, $action, $directory );
	$pipes   = array();
	// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- Explicit manual local proof only; preflight verifies this API, and only the returned process is reaped.
	$process = proc_open(
		$command,
		array(
			0 => array( 'file', 'NUL', 'r' ),
			1 => array( 'file', $directory . '/stdout.log', 'w' ),
			2 => array( 'file', $directory . '/stderr.log', 'w' ),
		),
		$pipes,
		__DIR__,
		null,
		array( 'bypass_shell' => true )
	);
	$child   = array(
		'process'   => $process,
		'directory' => $directory,
	);
	if ( ! is_resource( $process ) ) {
		adb_poll_reap( $child );
		throw new RuntimeException( 'Owned child creation failed' );
	}
	try {
		adb_poll_wait_marker( $directory . '/ready.json', 5 );
		$ready = json_decode( adb_poll_read_marker( $directory . '/ready.json' ), true );
		if ( ! is_array( $ready ) || empty( $ready['connection_id'] ) ) {
			throw new RuntimeException( 'Owned readiness witness missing' );
		}
		$child['connection_id'] = (int) $ready['connection_id'];
		return $child;
	} catch ( Throwable $error ) {
		$diagnostic = is_file( $directory . '/stderr.log' ) ? file_get_contents( $directory . '/stderr.log', false, null, 0, 2048 ) : '';
		adb_poll_reap( $child );
		// Preserve an error fingerprint without persisting SQL, credentials or paths.
		throw new RuntimeException( $error->getMessage() . '; owned stderr sha256=' . hash( 'sha256', $diagnostic ), 0, $error ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Fixed-label private CLI diagnostics, no raw stderr output.
	}
}

/**
 * Retrieves a completed owned result, then reaps resources in the caller's finally.
 *
 * @param array $child Owned process/manifest.
 * @return array
 * @throws RuntimeException On failure, expiry or unexpected process output.
 */
function adb_poll_result( array $child ) {
	adb_poll_wait_marker( $child['directory'] . '/result.json', 7 );
	$deadline = microtime( true ) + 3;
	do {
		$status = proc_get_status( $child['process'] );
		if ( ! $status['running'] ) {
			break;
		}
		usleep( 10000 );
	} while ( microtime( true ) < $deadline );
	if ( $status['running'] || 0 !== $status['exitcode'] || filesize( $child['directory'] . '/stderr.log' ) || filesize( $child['directory'] . '/stdout.log' ) ) {
		throw new RuntimeException( 'Owned worker failed or emitted unexpected output' );
	}
	$result = json_decode( adb_poll_read_marker( $child['directory'] . '/result.json' ), true );
	if ( ! is_array( $result ) || ! isset( $result['type'] ) ) {
		throw new RuntimeException( 'Owned worker result missing' );
	}
	return $result;
}
