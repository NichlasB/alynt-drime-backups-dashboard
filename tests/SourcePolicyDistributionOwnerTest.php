<?php
use PHPUnit\Framework\TestCase;
/** Actual proof construction with extension-free API doubles; not native evidence. */
final class SourcePolicyDistributionOwnerTest extends TestCase {
	/** @dataProvider cases */
	public function test_actual_default_owner_construction_and_fail_closed_cases($case) {
		$process=proc_open(array(PHP_BINARY,'-n',__DIR__.'/support/source-policy-distribution-worker.php',$case),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
		$this->assertIsResource($process);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
		$this->assertSame(0,proc_close($process),$err.$out);$this->assertSame('',$err);$this->assertMatchesRegularExpression('/\Achecks:[1-9][0-9]*\n\z/',$out);
	}
	public static function cases(){return array_map(static function($case){return array($case);},array('positive','old-model','foreign-source','wrong-job','prefixed-server'));}
}
