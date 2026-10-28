<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__.'/support/source-policy-bootstrap-scenarios.php';

/** Fixed PHP -n workers: no native extension, WordPress or database proof. */
final class SourcePolicyBootstrapFoundationTest extends TestCase {
	/** @dataProvider scenarios */
	public function test_private_bootstrap_contract_in_extension_free_worker($scenario) {
		$process = proc_open( array( PHP_BINARY, '-n', '-d', 'disable_functions=json_encode,json_decode', __DIR__ . '/support/source-policy-bootstrap-worker.php', $scenario ), array( 0 => array( 'pipe', 'r' ), 1 => array( 'pipe', 'w' ), 2 => array( 'pipe', 'w' ) ), $pipes );
		$this->assertIsResource( $process );
		fclose( $pipes[0] );
		$output = stream_get_contents( $pipes[1] );
		$error = stream_get_contents( $pipes[2] );
		fclose( $pipes[1] ); fclose( $pipes[2] );
		$this->assertSame( 0, proc_close( $process ), $error . $output );
		$this->assertSame('', $error);
		$this->assertTrue( self::valid_report( $output ), 'Expected one positive, bounded ASCII check-count report, without diagnostic noise.' );
	}
	public static function scenarios(){return array_map(static function($case){return array($case);},policy_bootstrap_scenarios());}

	/** @dataProvider reports */
	public function test_report_requires_one_bounded_positive_integer($report,$expected) {
		$this->assertSame($expected,self::valid_report($report));
	}
	private static function valid_report($report) {
		return 1===preg_match('/\Achecks:[1-9][0-9]{0,8}\n\z/',$report);
	}
	public static function reports() {
		return array(
			array("checks:1\n",true), array("checks:178\n",true), array("checks:999999999\n",true),
			array("checks:0\n",false), array("checks:-1\n",false), array("checks:01\n",false),
			array("checks:1.5\n",false), array("checks:1e3\n",false), array("checks:1000000000\n",false),
			array('checks:1',false), array(" checks:1\n",false), array("checks:1\nnoise",false),
			array("noise\nchecks:1\n",false), array("checks:1\nchecks:1\n",false), array("checks:1\r\n",false),
			array('{"checks":178}'."\n",false), array('',false), array("checks:1\n\n",false), array("checks:1\0\n",false),
		);
	}
}
