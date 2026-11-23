<?php
use PHPUnit\Framework\TestCase;

/** RAM-only launch/recovery logic; this bridge never executes a candidate. */
final class SourcePolicyPpaLaunchTest extends TestCase {

	public function test_connection_free_launch_and_retirement_ram_contracts() {
		$python = getenv( 'ADB_STATIC_ANALYSIS_PYTHON' ) ?: '/usr/bin/python3';
		$this->assertFileExists( $python, 'Use existing trusted Python; no install or skip.' );
		$process = proc_open(
			array( $python, '-I', '-S', '-B', __DIR__ . '/support/source_policy_ppa_launch_test.py' ),
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
			array( 'tests' => 30, 'failures' => 0, 'errors' => 0, 'skips' => 0 ),
			json_decode( $output, true )
		);
	}
}
