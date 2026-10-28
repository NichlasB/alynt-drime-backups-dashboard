<?php
use PHPUnit\Framework\TestCase;
require_once dirname(__DIR__).'/scripts/manual/source-policy-connected-cleanup-evidence.php';
require_once __DIR__.'/support/source-policy-connected-cleanup-fixtures.php';

/** Executes the actual private bridge with virtual observer DATA only. */
final class SourcePolicyConnectedCleanupBridgeTest extends TestCase {
 private function bridge($input,$run=null){
  $run=$run??str_repeat('a',32);$source=hash('sha256','isolated-cleanup-fixture');
  $p=proc_open(array(PHP_BINARY,'-n',dirname(__DIR__).'/scripts/manual/source-policy-connected-cleanup-bridge.php','--private-connected-cleanup-data',$run,$source),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
  $this->assertIsResource($p);fwrite($pipes[0],$input);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);return array(proc_close($p),$out,$err);
 }
 public function test_fixed_two_cases_complete_only_with_distinct_live_and_post_data_and_new_tokens(){
  $input='';$expected='';$now=0;$source=hash('sha256','isolated-cleanup-fixture');
  foreach(connected_cleanup_fixture_rows(true) as $case=>$row){$pid=$row['pid'];$input.='launch|'.$now++.'|'.$pid."\n".'feed|'.$now++.'|'.cleanup_fixture_wire($row).'live|'.$now++.'|'.$pid.",1,0,0\n".'finish|'.$now++.'|'.$pid.",0,0,0,0\n";
   $expected.="OK|output\nOK|held\nADBC2-ACK|".str_repeat('a',32).'|'.$source.'|'.$case.'|'.$pid."\nOK|".($case==='positive'?'done':'launch')."\n";
  }
  list($code,$out,$err)=$this->bridge($input);$this->assertSame(0,$code,$err);$this->assertSame('',$err);$this->assertSame($expected,$out);$this->assertStringNotContainsString('ADBC-ACK|',$out);
 }
 /** @dataProvider rejected_commands */
 public function test_malformed_context_scope_order_legacy_frames_and_incomplete_cohort_fail_closed($input){
  list($code,$out,$err)=$this->bridge($input);$this->assertSame(1,$code);$this->assertSame('',$err);$this->assertStringNotContainsString('ADBC2-ACK|',$out);
 }
 public static function rejected_commands(){
  $row=connected_cleanup_fixture_rows()['missing-table'];$legacy=cleanup_fixture_rows()['missing-table'];$held="launch|0|105\nfeed|1|".cleanup_fixture_wire($row);
  return array(array(''),array("launch|0|105\n"),array("launch|0|105\nfeed|1|".cleanup_fixture_wire($legacy)),array($held),array($held."live|2|105,1,1,0\n"),array($held."finish|2|105,0,0,0,0\n"),array($held."live|2|999,1,0,0\n"),array("launch|00|105\n"),array(str_repeat('x',17000)."\n"));
 }
 public function test_native_entry_rejects_unapproved_arguments_before_core_loading(){
  $p=proc_open(array(PHP_BINARY,'-n',dirname(__DIR__).'/scripts/manual/source-policy-connected-cleanup-worker.php','--not-approved'),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);$this->assertIsResource($p);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);$this->assertSame(1,proc_close($p));$this->assertSame('',$out);$this->assertSame('',$err);
 }
}
