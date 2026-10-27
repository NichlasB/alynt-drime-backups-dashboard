<?php
/**
 * One bounded native lock-wait failure and exact owned process/file cleanup.
 *
 * @package Alynt_Drime_Backups_Dashboard
 */

if ( ! defined( 'ADB_POLICY_PROOF' ) ) {
	exit( 1 );
}

/**
 * Starts/reaps only its returned child handle and owned temporary directory.
 *
 * @return void
 * @throws RuntimeException On any failed cleanup or proof.
 */
function adbp_lock_case() {
	global $argv, $wpdb, $groups, $owned_connections, $owned_child_pid;
	adbp_seed( array() );
	$before    = adbp_observe();
	$directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $argv[2] . 'lock_' . bin2hex( random_bytes( 6 ) );
	adbp_check( ! file_exists( $directory ) && mkdir( $directory ), 'fresh owned process directory' );
	$directory  = realpath( $directory );
	$process    = null;
	$clean_exit = true;
	try {
		$command = array( PHP_BINARY, '-n', '-d', 'extension_dir=' . ini_get( 'extension_dir' ), '-d', 'extension=mysqli', '-d', 'extension=mbstring', __DIR__ . '/source-policy-proof-lock-worker.php', '--confirmed-plugin-tester-owned-policy-proof', $argv[2], $directory );
		$pipes   = array();
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions -- Explicit owned private proof child only, no shell or external PID.
		$process = proc_open(
			$command,
			array(
				0 => array( 'file', 'NUL', 'r' ),
				1 => array( 'file', $directory . '/stdout', 'w' ),
				2 => array( 'file', $directory . '/stderr', 'w' ),
			),
			$pipes,
			__DIR__,
			null,
			array( 'bypass_shell' => true )
		);
		adbp_check( is_resource( $process ), 'owned lock process started' );
		$owned_child_pid = proc_get_status( $process )['pid'];
		$deadline        = microtime( true ) + 5;
		do {
			clearstatcache( true, $directory . '/ready' );
			if ( is_file( $directory . '/ready' ) ) {
				break;
			}
			$status = proc_get_status( $process );
			if ( ! $status['running'] ) {
				throw new RuntimeException( 'Owned blocker startup exited early' );
			}
			usleep( 10000 );
		} while ( microtime( true ) < $deadline );
		$witness = is_file( $directory . '/ready' ) ? file_get_contents( $directory . '/ready', false, null, 0, 64 ) : '';
		adbp_check( 1 === preg_match( '/\Alocked:([1-9][0-9]*)\z/', $witness, $matched ), 'owned locking witness' );
		$owned_connections[] = (int) $matched[1];
		$started             = microtime( true );
		adbp_error( adbp_save( $before['model']->revision(), 7, 'external_optional' ), 'source_policy_storage_error' );
		$elapsed = microtime( true ) - $started;
		adbp_check( $elapsed >= 0.8 && $elapsed < 4, 'real bounded server lock-wait failure' );
		adbp_check( array() === adbp_observe()['map'], 'timed-out statement made no mutation' );
	} finally {
		if ( is_resource( $process ) ) {
			adbp_check( false !== file_put_contents( $directory . '/release', 'release', LOCK_EX ), 'release owned blocker' );
			$deadline = microtime( true ) + 3;
			do {
				$status = proc_get_status( $process );
				if ( ! $status['running'] ) {
					break;
				}
				usleep( 10000 );
			} while ( microtime( true ) < $deadline );
			if ( $status['running'] ) {
				proc_terminate( $process );
				$deadline = microtime( true ) + 3;
				do {
					usleep( 10000 );
					$status = proc_get_status( $process );
				} while ( $status['running'] && microtime( true ) < $deadline );
				if ( $status['running'] ) {
					throw new RuntimeException( 'Owned blocker remains live; verify exact fixture manifest' );
				}
				$clean_exit = false;
			}
			proc_close( $process );
			$clean_exit = $clean_exit && 0 === $status['exitcode'] && "rolled-back\n" === file_get_contents( $directory . '/stdout' ) && 0 === filesize( $directory . '/stderr' );
		}
		foreach ( array( 'ready', 'release', 'stdout', 'stderr' ) as $name ) {
			$path = $directory . '/' . $name;
			if ( file_exists( $path ) ) {
				adbp_check( unlink( $path ), 'exact owned marker removed' );
			}
		}
		adbp_check( rmdir( $directory ), 'exact owned directory removed' );
		adbp_check( $clean_exit, 'owned rollback acknowledgement and clean process exit' );
	}
	$groups[] = 'native-lock-timeout-and-owned-process-cleanup';
}
