<?php
use PHPUnit\Framework\TestCase;
/** Actual proof construction with extension-free API doubles; not native evidence. */
final class SourcePolicyDistributionOwnerTest extends TestCase {
	/** @dataProvider cases */
	public function test_actual_bound_owner_construction_and_fail_closed_cases($case) {
		$process=proc_open(array(PHP_BINARY,'-n','-d','disable_functions=json_encode,json_decode',__DIR__.'/support/source-policy-distribution-worker.php',$case),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
		$this->assertIsResource($process);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
		$this->assertSame(0,proc_close($process),$err.$out);$this->assertSame('',$err);$this->assertMatchesRegularExpression('/\Achecks:[1-9][0-9]*\n\z/',$out);
	}
	public static function cases(){
		$cases=array('positive','old-model','foreign-source','wrong-job','unprefixed','double-prefix','newer-server','wrong-build','configured-charset','explicit-collation','helper','helper-collation','schema','late-context','late-frame','active-facts','active-charset','active-collation');
		foreach(array('model','platform','arch','php','client','core_commit','runtime_image','server_image','initial_mode','head','image','run')as$key)$cases[]='evidence-'.$key;
		foreach(array('core','database','table','host','port','user','charset','collate','transport','socket','db_host')as$key)$cases[]='frame-'.$key;
		foreach(array('GITHUB_WORKFLOW_REF','GITHUB_SHA','EXPECTED_SHA','GITHUB_RUN_ID','GITHUB_RUN_ATTEMPT','DISTRIBUTION_EXECUTION_APPROVED')as$key)$cases[]='job-'.$key;
		foreach(array('client_charset','connection_charset','result_charset','collation')as$key)$cases[]='session-'.$key;
		return array_map(static function($case){return array($case);},$cases);
	}
}
