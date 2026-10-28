<?php
use PHPUnit\Framework\TestCase;
require_once dirname(__DIR__).'/scripts/manual/source-policy-connected-cleanup-evidence.php';

final class SourcePolicyConnectedCleanupOperationTest extends TestCase {
 private function run_case($case,$fault){
  $p=proc_open(array(PHP_BINARY,'-n',__DIR__.'/support/source-policy-connected-cleanup-owner-worker.php',$case,$fault),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
  $this->assertIsResource($p);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);return array(proc_close($p),$out,$err);
 }
 /** @dataProvider actual_cases */
 public function test_shared_producer_observes_actual_dormant_owner_and_cleanup_under_api_doubles($case){
  list($exit,$out,$err)=$this->run_case($case,'normal');$this->assertSame(0,$exit,$err);$this->assertSame('',$err);
  $row=Alynt_Drime_Backups_Dashboard_Connected_Cleanup_Evidence::parse($out);$this->assertIsArray($row);
  $summary=Alynt_Drime_Backups_Dashboard_Connected_Cleanup_Evidence::inspect($row,str_repeat('a',32),$case,str_repeat('b',64),(int)$row['pid']);
  $this->assertIsArray($summary);$this->assertSame('divergent',$summary['counter_accounting']);$this->assertSame('pending-live-reconciliation',$summary['release_evidence']);$this->assertSame('not-established',$summary['native_qualification']);
  $this->assertSame($case==='positive'?'active':'unavailable',$row['outcome']);$this->assertSame('1',$row['ack']);$this->assertSame($case==='positive'?'1':'0',$row['roundtrip']);$this->assertSame($case==='positive'?'1':'0',$row['seal']);
  $this->assertFalse(Alynt_Drime_Backups_Dashboard_Cleanup_Evidence::parse($out));
 }
 public static function actual_cases(){return array(array('missing-table'),array('positive'));}
 /** @dataProvider faults */
 public function test_always_unavailable_bad_roundtrip_results_close_and_effects_fail_non_vacuously($fault){
  list($exit,$out,$err)=$this->run_case('positive',$fault);$this->assertSame(1,$exit);$this->assertSame('',$out);$this->assertStringContainsString('isolated ADBC2 assertion:',$err);
  $this->assertStringNotContainsString('Fatal error',$err);$this->assertStringNotContainsString('owned-private-password',$err);
 }
 public static function faults(){return array(array('unavailable'),array('roundtrip'),array('flush'),array('close'),array('stats'));}
}
