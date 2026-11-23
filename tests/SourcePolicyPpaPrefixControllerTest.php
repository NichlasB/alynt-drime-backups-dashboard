<?php
use PHPUnit\Framework\TestCase;

/** Isolated manual-owner API/RAM tests, not a physical materialization proof. */
final class SourcePolicyPpaPrefixControllerTest extends TestCase {

	public function test_prefix_proof_controller_and_fixed_entry_ram_contracts() {
		$python = getenv( 'ADB_STATIC_ANALYSIS_PYTHON' ) ?: '/usr/bin/python3';
		$this->assertFileExists( $python, 'Use existing trusted Python; no install or skip.' );
		$process = proc_open(
			array( $python, '-I', '-S', '-B', __DIR__ . '/support/source_policy_ppa_prefix_controller_test.py' ),
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
			array( 'tests' => 15, 'failures' => 0, 'errors' => 0, 'skips' => 0 ),
			json_decode( $output, true )
		);
	}
}
