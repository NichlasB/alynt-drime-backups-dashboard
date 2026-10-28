<?php
use PHPUnit\Framework\TestCase;
require_once dirname(__DIR__).'/scripts/manual/source-policy-cleanup-diagnostic.php';
require_once __DIR__.'/support/source-policy-cleanup-fixtures.php';

/** Diagnostics are bounded non-secret DATA, never terminal acceptance. */
final class SourcePolicyCleanupDiagnosticTest extends TestCase {
 public function test_observed_connected_counter_mismatches_remain_independent_refusals() {
  // Replay only non-secret measured DATA with isolated correlation, never a native receipt.
  $base=cleanup_fixture_rows()['missing-table'];$pid=(int)$base['pid'];
  foreach(array(array('pconnect_success'=>'1'),array('active_connections'=>Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::WRAP),array('pconnect_success'=>'1','active_connections'=>Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::WRAP)) as $changes){
   $row=$base;
   foreach(array('bootstrap','after') as $field){foreach($changes as $counter=>$value){cleanup_fixture_counter($row,$field,$counter,$value);}}
   $before=$row;$wire=$this->render($row,'missing-table',$pid);
   $this->assertFalse(Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::matches($row,str_repeat('a',32),'missing-table',hash('sha256','isolated-cleanup-fixture'),$pid));
   $this->assertStringStartsWith('ADBC-DIAG1|missing-table|'.$pid.'|1|'.$row['bootstrap'].'|'.$row['after'].'|',$wire);
   $this->assertSame($before,$row);$this->assertFalse(Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::parse($wire));
  }
  $row=$base;$row['bootstrap']='1,0,10,6,6,0,0,0,0,1,18446744073709551615';$row['after']=$row['bootstrap'];$row['elapsed']='28';
  $this->assertFalse(Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::matches($row,str_repeat('a',32),'missing-table',hash('sha256','isolated-cleanup-fixture'),$pid));
  $this->assertStringContainsString('|'.$row['bootstrap'].'|'.$row['after'].'|1,unavailable,0,1,1,1,1,0,1,1,1,1,1,1,closed,0,8,0,0,0,28', $this->render($row,'missing-table',$pid));
 }
 public function test_rejected_observations_remain_rejected_and_counters_are_unchanged() {
  foreach(cleanup_fixture_rows() as $case=>$row){
   $pid=(int)$row['pid'];$row['steps']='4';$before=$row;$wire=$this->render($row,$case,$pid);
   $this->assertStringStartsWith('ADBC-DIAG1|'.$case.'|'.$pid.'|1|'.$row['bootstrap'].'|'.$row['after'].'|',$wire);
   $this->assertSame($before,$row);$this->assertLessThanOrEqual(256,strlen($wire));
   $this->assertStringNotContainsString(str_repeat('a',32),$wire);$this->assertStringNotContainsString(hash('sha256','isolated-cleanup-fixture'),$wire);
   $this->assertFalse(Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::parse($wire));
   $this->assertFalse(Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::matches($row,str_repeat('a',32),$case,hash('sha256','isolated-cleanup-fixture'),$pid));
  }
 }
 /** @dataProvider unsafe_fields */
 public function test_unsafe_values_never_escape_or_get_coerced($key,$value) {
  $row=cleanup_fixture_rows()['missing-table'];$row[$key]=$value;
  $this->assertSame("ADBC-DIAG-UNSAFE\n",$this->render($row,'missing-table',105));
 }
 public static function unsafe_fields(){return array(array('outcome','private-secret'),array('state','private-secret'),array('steps','private-secret'),array('bootstrap','private-secret'),array('after','0,0'),array('source','private-secret'),array('run','private-secret'),array('case','private-secret'),array('profile','private-secret'),array('pid','104'),array('version','secret'),array('elapsed','100000'));}
 public function test_objects_unknown_context_and_oversize_fail_closed() {
  $row=cleanup_fixture_rows()['missing-table'];$row['ack']=new class {public function __toString(){throw new RuntimeException('No coercion');}};
  $this->assertSame("ADBC-DIAG-UNSAFE\n",$this->render($row,'missing-table',105));
  $row=cleanup_fixture_rows()['missing-table'];$row['bootstrap']=implode(',',array_fill(0,11,'18446744073709551615'));$row['after']=$row['bootstrap'];
  $this->assertSame("ADBC-DIAG-UNSAFE\n",$this->render($row,'missing-table',105));
  $this->assertSame("ADBC-DIAG-UNSAFE\n",Alynt_Drime_Backups_Dashboard_Cleanup_Diagnostic::format(cleanup_fixture_rows()['missing-table'],'bad','missing-table',hash('sha256','isolated-cleanup-fixture'),105));
 }
 private function render($row,$case,$pid){return Alynt_Drime_Backups_Dashboard_Cleanup_Diagnostic::format($row,str_repeat('a',32),$case,hash('sha256','isolated-cleanup-fixture'),$pid);}
}
