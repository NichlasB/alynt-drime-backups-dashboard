<?php
use PHPUnit\Framework\TestCase;

/** The manual installed-core proof must never run accidentally in hosted QA. */
final class SourcePolicyCoreContextProofGateTest extends TestCase {
	/** @dataProvider refused_invocations */
	public function test_cli_gate_refuses_before_any_installed_core_read( $arguments ) {
		$script = dirname( __DIR__ ) . '/scripts/manual/source-policy-core-context-proof.php';
		$process = proc_open( array_merge( array( PHP_BINARY, '-n', $script ), $arguments ), array( 0 => array( 'pipe', 'r' ), 1 => array( 'pipe', 'w' ), 2 => array( 'pipe', 'w' ) ), $pipes );
		$this->assertIsResource( $process );
		fclose( $pipes[0] );
		$output = stream_get_contents( $pipes[1] );
		$error = stream_get_contents( $pipes[2] );
		fclose( $pipes[1] );
		fclose( $pipes[2] );
		$this->assertSame( 1, proc_close( $process ) );
		$this->assertSame( '', $output );
		$this->assertSame( "Requires confirmed Plugin Tester zero-SQL context proof scope\n", $error );
	}
	public static function refused_invocations() {
		return array( array( array() ), array( array( '--wrong-scope' ) ), array( array( '--confirmed-plugin-tester-zero-sql-context-proof', '--extra' ) ) );
	}
}
