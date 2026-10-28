<?php
use PHPUnit\Framework\TestCase;
require_once dirname(__DIR__).'/scripts/manual/source-policy-cleanup-evidence.php';
require_once __DIR__.'/support/source-policy-cleanup-fixtures.php';

/** Early CLI refusal and socket-free bridge, never installed WordPress or native IO. */
final class SourcePolicyCleanupNativeGateTest extends TestCase {
 /** @dataProvider bad_arguments */
 public function test_native_entry_refuses_before_site_access($arguments) {
  list($exit,$out,$err)=$this->worker('source-policy-cleanup-native-worker.php',$arguments,'');
  $this->assertSame(1,$exit);$this->assertSame('',$out);$this->assertSame("Requires confirmed fixed Plugin Tester per-case cleanup scope\n",$err);
 }
 public static function bad_arguments(){
  $valid=array('--confirmed-plugin-tester-per-case-cleanup','adbb_0123456789ab_',str_repeat('a',32),str_repeat('b',64),'positive');$sets=array(array(array()));
  foreach(array(0=>'wrong',1=>'wp_',2=>'bad',3=>'bad',4=>'arbitrary') as $key=>$value){$args=$valid;$args[$key]=$value;$sets[]=array($args);}return $sets;
 }
 public function test_bridge_executes_all_seven_virtual_cases_and_preserves_exact_ack_order() {
  $commands='';$expected='';$now=0;$run=str_repeat('a',32);$source=hash('sha256','isolated-cleanup-fixture');
  foreach(cleanup_fixture_rows() as $case=>$row){$pid=$row['pid'];
   $commands.='launch|'.$now++.'|'.$pid."\n";$expected.="OK|output\n";
   $commands.='feed|'.$now++.'|'.rtrim(cleanup_fixture_wire($row),"\n")."\n";$expected.="OK|held\n";
   $commands.='live|'.$now++.'|'.$pid.",1,0,0\n";$expected.='ADBC-ACK|'.$run.'|'.$case.'|'.$pid."\n";
   $commands.='finish|'.$now++.'|'.$pid.",0,0,0,0\n";$expected.='OK|'.('positive'===$case?'done':'launch')."\n";
  }
  list($exit,$out,$err)=$this->worker('source-policy-cleanup-bridge.php',array('--private-cleanup-data-bridge',$run,$source),$commands);
  $this->assertSame(0,$exit,$err);$this->assertSame('',$err);$this->assertSame($expected,$out);
 }
 public function test_bridge_rejects_incomplete_and_foreign_session_transcripts() {
  $held="launch|0|100\nfeed|1|".rtrim(cleanup_fixture_wire(cleanup_fixture_rows()['default']),"\n")."\n";
  foreach(array('launch|0|100',$held."live|2|100,1,1,0\n",$held."live|2|100,1,0,1\n",str_repeat('x',17001)) as $input){
   list($exit,$out,$err)=$this->worker('source-policy-cleanup-bridge.php',array('--private-cleanup-data-bridge',str_repeat('a',32),str_repeat('b',64)),$input);
   $this->assertSame(1,$exit);$this->assertSame('',$err);$this->assertStringNotContainsString('ADBC-ACK|',$out);
  }
 }
 private function worker($file,$arguments,$input){
  $p=proc_open(array_merge(array(PHP_BINARY,'-n','-d','disable_functions=json_encode,json_decode',dirname(__DIR__).'/scripts/manual/'.$file),$arguments),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
  $this->assertIsResource($p);fwrite($pipes[0],$input);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);return array(proc_close($p),$out,$err);
 }
}
