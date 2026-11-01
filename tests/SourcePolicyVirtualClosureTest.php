<?php
use PHPUnit\Framework\TestCase;

final class SourcePolicyVirtualClosureTest extends TestCase {
 public function test_actual_synthetic_only_model_contracts() {
  $python=getenv('ADB_STATIC_ANALYSIS_PYTHON')?:'/usr/bin/python3';
  $p=proc_open(array($python,'-I','-B',__DIR__.'/support/source_policy_virtual_closure_test.py'),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
  $this->assertIsResource($p);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
  $this->assertSame(0,proc_close($p),$err);$this->assertSame('',$err);
  $result=json_decode($out,true);$resource=$result['resource'];unset($result['resource']);
  $this->assertSame(array('tests'=>14,'failures'=>0,'errors'=>0,'network_calls'=>0),$result);
  $this->assertSame(65536,$resource['nodes']);$this->assertLessThan(536870912,$resource['python_peak_bytes']);$this->assertLessThan(30000,$resource['elapsed_ms']);
 }
 public function test_actual_refusal_provenance_contracts() {
  $python=getenv('ADB_STATIC_ANALYSIS_PYTHON')?:'/usr/bin/python3';
  $p=proc_open(array($python,'-I','-B',__DIR__.'/support/source_policy_ubi_provenance_test.py'),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
  $this->assertIsResource($p);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
  $this->assertSame(0,proc_close($p),$err);$this->assertSame('',$err);
  $this->assertSame(array('tests'=>5,'failures'=>0,'errors'=>0,'network_calls'=>0),json_decode($out,true));
 }
 public function test_model_has_no_runtime_consumer_and_real_profile_is_empty() {
  $root=dirname(__DIR__);
  foreach(array('scripts/manual/source_policy_linux_minimum_static.py','scripts/ci/source-policy-legacy-ubi-static.py','.github/workflows/source-policy-legacy-ubi-static.yml','alynt-drime-backups-dashboard.php') as $file)$this->assertStringNotContainsString('source_policy_virtual_closure',file_get_contents($root.'/'.$file));
  $this->assertStringContainsString('return ()',file_get_contents($root.'/scripts/manual/source_policy_virtual_closure.py'));
 }
}
