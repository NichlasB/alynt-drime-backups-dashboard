<?php
use PHPUnit\Framework\TestCase;
require_once dirname(__DIR__).'/scripts/manual/source-policy-connected-cleanup-cohort.php';
require_once __DIR__.'/support/source-policy-connected-cleanup-fixtures.php';

/** Virtual chunks/clocks and observer DATA only, never an installed/native proof. */
final class SourcePolicyConnectedCleanupCohortTest extends TestCase {
 private function cohort(){return new Alynt_Drime_Backups_Dashboard_Connected_Cleanup_Cohort(str_repeat('a',32),hash('sha256','isolated-cleanup-fixture'),0);}
 private function held(){ $c=$this->cohort();$c->launch(105,0);$this->assertTrue($c->feed('out',cleanup_fixture_wire(connected_cleanup_fixture_rows(true)['missing-table']),1));return $c; }
 /** @dataProvider accounting_profiles */
 public function test_both_actual_isolated_owner_cases_need_independent_live_and_post_data($divergent){
  $c=$this->cohort();$now=0;
  foreach(connected_cleanup_fixture_rows($divergent) as $case=>$row){
   $pid=(int)$row['pid'];$launch=$c->launch($pid,$now++);$this->assertSame($case,$launch['case']);$wire=cleanup_fixture_wire($row);
   $this->assertTrue($c->feed('out',substr($wire,0,20),$now++));$this->assertTrue($c->feed('out',substr($wire,20),$now++));
   $this->assertSame('pending',$c->status()['release_evidence']);$this->assertSame($divergent,$c->status()['counter_review_required']);
   $token=$c->live(cleanup_fixture_observation($pid,true),$now++);$this->assertSame('ADBC2-ACK|'.str_repeat('a',32).'|'.hash('sha256','isolated-cleanup-fixture').'|'.$case.'|'.$pid."\n",$token);
   $this->assertStringNotContainsString('ADBC-ACK|',$token);$this->assertSame('pending',$c->status()['release_evidence']);
   $this->assertTrue($c->finish(0,cleanup_fixture_observation($pid,false),$now++));
   if($case==='missing-table'){$this->assertSame(1,$c->status()['completed']);$this->assertSame('launch',$c->status()['phase']);$this->assertSame('pending',$c->status()['release_evidence']);}
  }
  $status=$c->status();$this->assertSame('done',$status['phase']);$this->assertSame(2,$status['completed']);$this->assertSame('complete',$status['release_evidence']);
  $this->assertSame(array('missing-table'=>$divergent?'divergent':'nominal','positive'=>$divergent?'divergent':'nominal'),$status['counter_accounting']);
  $this->assertSame('not-established',$status['native_qualification']);$this->assertSame($divergent,$status['counter_review_required']);
 }
 public static function accounting_profiles(){return array(array(false),array(true));}
 public function test_divergence_review_cannot_be_cleared_by_a_later_nominal_case(){
  $c=$this->cohort();$now=0;$rows=connected_cleanup_fixture_rows(true);$rows['positive']=connected_cleanup_fixture_rows(false)['positive'];
  foreach($rows as $case=>$row){$pid=(int)$row['pid'];$c->launch($pid,$now++);$this->assertTrue($c->feed('out',cleanup_fixture_wire($row),$now++));$c->live(cleanup_fixture_observation($pid,true),$now++);$this->assertTrue($c->finish(0,cleanup_fixture_observation($pid,false),$now++));}
  $this->assertSame(array('missing-table'=>'divergent','positive'=>'nominal'),$c->status()['counter_accounting']);$this->assertTrue($c->status()['counter_review_required']);$this->assertSame('not-established',$c->status()['native_qualification']);
 }
 /** @dataProvider observer_faults */
 public function test_counter_anomalies_never_substitute_for_live_or_post_quiescence($stage,$key,$value){
  $c=$this->held();$row=cleanup_fixture_observation(105,$stage==='live');$row[$key]=$value;
  if($stage==='live')$this->assertFalse($c->live($row,2));else{$this->assertIsString($c->live(cleanup_fixture_observation(105,true),2));$this->assertFalse($c->finish(0,$row,3));}
  $this->assertSame('unconfirmed',$c->status()['phase']);$this->assertSame('not-established',$c->status()['native_qualification']);$this->assertTrue($c->status()['counter_review_required']);$this->assertFalse($c->launch(106,4));
 }
 public static function observer_faults(){
  $rows=array();foreach(array('live','post') as $stage)foreach(array('pid'=>999,'scope'=>'database-local','sessions'=>1,'transactions'=>1,'alive'=>'true','extra'=>'private-secret') as $key=>$value)$rows[]=array($stage,$key,$value);return $rows;
 }
 public function test_wrong_case_no_live_missing_positive_replay_and_bad_exit_remain_unconfirmed(){
  $c=$this->cohort();$c->launch(105,0);$this->assertFalse($c->feed('out',cleanup_fixture_wire(connected_cleanup_fixture_rows()['positive']),1));
  $c=$this->held();$this->assertFalse($c->finish(0,cleanup_fixture_observation(105,false),2));
  $c=$this->held();$c->live(cleanup_fixture_observation(105,true),2);$this->assertFalse($c->finish(1,cleanup_fixture_observation(105,false),3));
  $c=$this->held();$c->live(cleanup_fixture_observation(105,true),2);$c->finish(0,cleanup_fixture_observation(105,false),3);$this->assertFalse($c->launch(105,4));
  $c=$this->held();$this->assertFalse($c->feed('out',"extra\n",2));
  $c=$this->held();$c->live(cleanup_fixture_observation(105,true),2);$this->assertFalse($c->live(cleanup_fixture_observation(105,true),3));
  $c=$this->held();$c->live(cleanup_fixture_observation(105,true),2);$c->finish(0,cleanup_fixture_observation(105,false),3);$c->launch(106,4);
  $positive=connected_cleanup_fixture_rows(true)['positive'];$positive['outcome']='unavailable';$this->assertFalse($c->feed('out',cleanup_fixture_wire($positive),5));$this->assertSame(1,$c->status()['completed']);
 }
 public function test_bounded_pipes_truncation_error_redaction_and_one_deadline(){
  $c=$this->cohort();$c->launch(105,0);$this->assertFalse($c->feed('out',str_repeat('x',16385),1));$this->assertSame(0,$c->status()['stdout_bytes']);
  $c=$this->cohort();$c->launch(105,0);$this->assertTrue($c->feed('out',str_repeat('x',16384),1));$this->assertFalse($c->feed('out','x',2));
  $c=$this->cohort();$c->launch(105,0);$this->assertFalse($c->feed('err',str_repeat('private-secret',1000),1));$this->assertLessThanOrEqual(4097,$c->status()['stderr_bytes']);$this->assertStringNotContainsString('private',serialize($c->status()));
  $wire=cleanup_fixture_wire(connected_cleanup_fixture_rows()['missing-table']);$c=$this->cohort();$c->launch(105,0);$this->assertFalse($c->feed('out',$wire.$wire,1));
  $c=$this->cohort();$c->launch(105,0);$c->feed('out',rtrim($wire),1);$this->assertFalse($c->live(cleanup_fixture_observation(105,true),2));
  $c=$this->held();$c->live(cleanup_fixture_observation(105,true),2);$c->finish(0,cleanup_fixture_observation(105,false),3);$this->assertIsArray($c->launch(106,24999));$this->assertFalse($c->feed('out','',25000));
  $c=$this->cohort();$c->launch(105,0);$c->feed('out','',10);$this->assertFalse($c->feed('out','',9));
 }
 public function test_bad_context_clock_and_observer_types_do_not_coerce(){
  foreach(array(array(new stdClass(),str_repeat('b',64),0),array(str_repeat('a',32),new stdClass(),0),array(str_repeat('a',32),str_repeat('b',64),'0')) as $args){$c=new Alynt_Drime_Backups_Dashboard_Connected_Cleanup_Cohort(...$args);$this->assertFalse($c->launch(105,0));}
  $c=$this->cohort();$this->assertFalse($c->launch('105',0));$c=$this->cohort();$this->assertFalse($c->launch(105,'0'));
  $c=$this->held();$row=cleanup_fixture_observation(105,true);$row['sessions']='0';$this->assertFalse($c->live($row,2));
 }
}
