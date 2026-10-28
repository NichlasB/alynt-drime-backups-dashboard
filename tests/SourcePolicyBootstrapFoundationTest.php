<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__.'/support/source-policy-bootstrap-scenarios.php';

/** Fixed PHP -n workers: no native extension, WordPress or database proof. */
final class SourcePolicyBootstrapFoundationTest extends TestCase {
	/** @dataProvider scenarios */
	public function test_private_bootstrap_contract_in_extension_free_worker($scenario) {
		$process = proc_open( array( PHP_BINARY, '-n', __DIR__ . '/support/source-policy-bootstrap-worker.php', $scenario ), array( 0 => array( 'pipe', 'r' ), 1 => array( 'pipe', 'w' ), 2 => array( 'pipe', 'w' ) ), $pipes );
		$this->assertIsResource( $process );
		fclose( $pipes[0] );
		$output = stream_get_contents( $pipes[1] );
		$error = stream_get_contents( $pipes[2] );
		fclose( $pipes[1] ); fclose( $pipes[2] );
		$this->assertSame( 0, proc_close( $process ), $error . $output );
		$this->assertSame('', $error);
		$this->assertGreaterThan( 0, json_decode( $output, true )['checks'] );
	}
	public static function scenarios(){return array_map(static function($case){return array($case);},policy_bootstrap_scenarios());}
}
