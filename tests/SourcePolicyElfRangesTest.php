<?php
use PHPUnit\Framework\TestCase;

/** Synthetic member DATA only; never loads or acquires a candidate artifact. */
final class SourcePolicyElfRangesTest extends TestCase {

	public function test_pure_collector_oracle_parity_and_fail_closed_controls() {
		$python = getenv( 'ADB_STATIC_ANALYSIS_PYTHON' ) ?: '/usr/bin/python3';
		$this->assertFileExists( $python, 'Use existing trusted Python; do not install or skip.' );
		$process = proc_open(
			array( $python, '-I', '-S', '-B', __DIR__ . '/support/source_policy_elf_ranges_test.py' ),
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
			array( 'tests' => 21, 'failures' => 0, 'errors' => 0, 'skips' => 0 ),
			json_decode( $output, true )
		);
	}
}
