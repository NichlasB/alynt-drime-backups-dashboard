<?php
use PHPUnit\Framework\TestCase;

/** Synthetic immutable DEB preparation only, never an artifact proof dispatch. */
final class SourcePolicyDebReplayTest extends TestCase {

	public function test_shared_walker_and_dormant_replay_commit_barriers() {
		$python = getenv( 'ADB_STATIC_ANALYSIS_PYTHON' ) ?: '/usr/bin/python3';
		$this->assertFileExists( $python, 'Use existing trusted Python; no install or skip.' );
		$process = proc_open(
			array( $python, '-I', '-S', '-B', __DIR__ . '/support/source_policy_deb_replay_test.py' ),
			array( 0 => array( 'pipe', 'r' ), 1 => array( 'pipe', 'w' ), 2 => array( 'pipe', 'w' ) ),
			$pipes
		);
		$this->assertIsResource( $process );
		fclose( $pipes[0] );
		$output = stream_get_contents( $pipes[1] );
		$error  = stream_get_contents( $pipes[2] );
		fclose( $pipes[1] );
		fclose( $pipes[2] );
		$this->assertSame( 0, proc_close( $process ), $error );
		$this->assertSame( '', $error );
		$this->assertSame(
			array( 'tests' => PHP_OS_FAMILY === 'Linux' ? 19 : 18, 'failures' => 0, 'errors' => 0, 'skips' => 0, 'linux_codecs' => PHP_OS_FAMILY === 'Linux' ),
			json_decode( $output, true )
		);
	}
}
