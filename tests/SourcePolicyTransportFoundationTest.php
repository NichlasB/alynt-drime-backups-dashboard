<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__.'/support/source-policy-transport-cases.php';

/** API/IO doubles under PHP -n only, never native transport or core proof. */
final class SourcePolicyTransportFoundationTest extends TestCase {
	/** @dataProvider scenarios */
	public function test_fixed_transport_and_core_helper_orchestration($scenario) {
		$process=proc_open(array(PHP_BINARY,'-n',__DIR__.'/support/source-policy-bootstrap-worker.php',$scenario),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
		$this->assertIsResource($process);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
		$this->assertSame(0,proc_close($process),$err.$out);$this->assertSame('',$err);$this->assertMatchesRegularExpression('/\Achecks:[1-9][0-9]{0,8}\n\z/',$out);
	}
	public static function scenarios(){return array_map(static function($case){return array($case);},policy_transport_scenarios());}
}
