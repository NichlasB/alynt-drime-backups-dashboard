<?php
use PHPUnit\Framework\TestCase;

/** Synthetic APIs only: exercise bootstrap, final admission and later operations. */
final class SourcePolicyMinimumLabReceiverTest extends TestCase {
	/** @dataProvider scenarios */
	public function test_bound_lab_receiver($scenario) {
		$process=proc_open(array(PHP_BINARY,'-n','-d','disable_functions=json_encode,json_decode',__DIR__.'/support/source-policy-minimum-lab-worker.php',$scenario),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
		$this->assertIsResource($process); fclose($pipes[0]);
		$out=stream_get_contents($pipes[1]); $err=stream_get_contents($pipes[2]); fclose($pipes[1]); fclose($pipes[2]);
		$this->assertSame(0,proc_close($process),$err.$out); $this->assertSame('',$err);
		$this->assertMatchesRegularExpression('/\Achecks:[1-9][0-9]*\n\z/',$out);
	}
	public static function scenarios() {
		$cases=array('positive','default','image','core','run','endpoint','mode','server','context','late-context','late-frame','closed','active-context','active-facts');
		foreach(array('model','platform','arch','php','client','core_commit','server_image','initial_mode','head','image')as$key)$cases[]='evidence-'.$key;
		return array_map(static function($value){return array($value);},$cases);
	}
}
