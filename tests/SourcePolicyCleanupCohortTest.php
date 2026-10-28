<?php
use PHPUnit\Framework\TestCase;
require_once dirname(__DIR__).'/scripts/manual/source-policy-cleanup-cohort.php';
require_once __DIR__.'/support/source-policy-cleanup-fixtures.php';

/** Virtual pipes/clocks/observer DATA only; no native worker hold or SQL proof. */
final class SourcePolicyCleanupCohortTest extends TestCase {
	public function test_complete_positive_cohort_uses_fresh_cases_and_live_then_post_checks() {
		$cohort=$this->cohort();$now=0;
		foreach(cleanup_fixture_rows() as $case=>$row){
			$pid=(int)$row['pid'];$launch=$cohort->launch($pid,$now++);$this->assertSame($case,$launch['case']);
			$wire=cleanup_fixture_wire($row);$this->assertTrue($cohort->feed('out',substr($wire,0,15),$now++));$this->assertTrue($cohort->feed('out',substr($wire,15),$now++));
			$this->assertSame('held',$cohort->status()['phase']);$this->assertSame('ADBC-ACK|'.str_repeat('a',32).'|'.$case.'|'.$pid."\n",$cohort->live(cleanup_fixture_observation($pid,true),$now++));
			$this->assertTrue($cohort->finish(0,cleanup_fixture_observation($pid,false),$now++));
		}
		$this->assertSame('done',$cohort->status()['phase']);$this->assertSame(7,$cohort->status()['completed']);$this->assertFalse($cohort->launch(999,$now));
	}
	/** @dataProvider observer_faults */
	public function test_live_or_post_observation_is_independent_and_all_session($stage,$key,$value) {
		$c=$this->held();$live=cleanup_fixture_observation(100,true);$post=cleanup_fixture_observation(100,false);
		if('live'===$stage){$live[$key]=$value;$this->assertFalse($c->live($live,2));}else{$this->assertIsString($c->live($live,2));$post[$key]=$value;$this->assertFalse($c->finish(0,$post,3));}
		$this->assertSame('unconfirmed',$c->status()['phase']);$this->assertFalse($c->launch(101,4));
	}
	public static function observer_faults() {
		$rows=array();foreach(array('live','post') as $stage)foreach(array('scope'=>'database-local','sessions'=>1,'transactions'=>1,'pid'=>101,'alive'=>'true') as $key=>$value)$rows[]=array($stage,$key,$value);return $rows;
	}
	/** @dataProvider ordering_faults */
	public function test_order_deadline_and_no_retry($fault) {
		$c=$this->cohort();$this->assertIsArray($c->launch(100,0));$row=cleanup_fixture_rows()['default'];
		if('duplicate-launch'===$fault)$result=$c->launch(101,1);
		elseif('early-exit'===$fault)$result=$c->finish(0,cleanup_fixture_observation(100,false),1);
		elseif('no-record'===$fault)$result=$c->live(cleanup_fixture_observation(100,true),1);
		elseif('deadline'===$fault)$result=$c->feed('out',cleanup_fixture_wire($row),25000);
		elseif('clock-reversal'===$fault){$this->assertTrue($c->feed('out','',10));$result=$c->feed('out','',9);}
		else {$row['case']='positive';$result=$c->feed('out',cleanup_fixture_wire($row),1);}
		$this->assertFalse($result);$this->assertSame('unconfirmed',$c->status()['phase']);$this->assertFalse($c->launch(102,25001));
	}
	public static function ordering_faults(){return array_map(static function($v){return array($v);},array('duplicate-launch','early-exit','no-record','deadline','clock-reversal','wrong-case'));}
	public function test_duplicate_records_extra_output_and_bad_exit_never_complete() {
		$c=$this->held();$this->assertFalse($c->feed('out',"extra\n",2));
		$c=$this->cohort();$c->launch(100,0);$wire=cleanup_fixture_wire(cleanup_fixture_rows()['default']);$this->assertFalse($c->feed('out',$wire.$wire,1));
		$c=$this->held();$c->live(cleanup_fixture_observation(100,true),2);$this->assertFalse($c->finish(1,cleanup_fixture_observation(100,false),3));
		$c=$this->held();$c->live(cleanup_fixture_observation(100,true),2);$c->finish(0,cleanup_fixture_observation(100,false),3);$this->assertFalse($c->launch(100,4));
	}
	public function test_pipe_caps_truncation_and_secret_discard() {
		$c=$this->cohort();$c->launch(100,0);$this->assertFalse($c->feed('out',str_repeat('x',16385),1));$this->assertSame(0,$c->status()['stdout_bytes']);
		$c=$this->cohort();$c->launch(100,0);$this->assertTrue($c->feed('out',str_repeat('x',16384),1));$this->assertFalse($c->feed('out','x',2));$this->assertSame(16384,$c->status()['stdout_bytes']);
		$c=$this->cohort();$c->launch(100,0);$this->assertFalse($c->feed('err',str_repeat('private-secret',1000),1));$this->assertStringNotContainsString('private',serialize($c->status()));$this->assertLessThanOrEqual(4097,$c->status()['stderr_bytes']);
		$c=$this->cohort();$c->launch(100,0);$c->feed('out',rtrim(cleanup_fixture_wire(cleanup_fixture_rows()['default'])),1);$this->assertFalse($c->live(cleanup_fixture_observation(100,true),2));
	}
	public function test_original_deadline_does_not_restart_after_a_case() {
		$c=$this->held();$c->live(cleanup_fixture_observation(100,true),2);$this->assertTrue($c->finish(0,cleanup_fixture_observation(100,false),3));
		$this->assertIsArray($c->launch(101,24999));$this->assertFalse($c->feed('out','',25000));$this->assertSame(1,$c->status()['completed']);
	}
	public function test_invalid_context_and_observer_types_never_coerce() {
		$bad=new class {public function __toString(){throw new RuntimeException('no coercion');}};
		foreach(array(array($bad,str_repeat('b',64),0),array(str_repeat('a',32),$bad,0),array(str_repeat('a',32),str_repeat('b',64),'0')) as $args){$c=new Alynt_Drime_Backups_Dashboard_Cleanup_Cohort(...$args);$this->assertFalse($c->launch(100,0));}
		foreach(array('sessions'=>'0','transactions'=>'0','alive'=>false,'extra'=>0) as $key=>$value){$c=$this->held();$row=cleanup_fixture_observation(100,true);$row[$key]=$value;$this->assertFalse($c->live($row,2));}
		$c=$this->cohort();$this->assertFalse($c->launch('100',0));$c=$this->cohort();$this->assertFalse($c->launch(100,'0'));
	}
	private function cohort(){return new Alynt_Drime_Backups_Dashboard_Cleanup_Cohort(str_repeat('a',32),hash('sha256','isolated-cleanup-fixture'),0);}
	private function held(){$c=$this->cohort();$c->launch(100,0);$this->assertTrue($c->feed('out',cleanup_fixture_wire(cleanup_fixture_rows()['default']),1));return $c;}
}
