<?php
use PHPUnit\Framework\TestCase;

final class SourcePolicyLegacyUbiStaticTest extends TestCase {
 public function test_actual_offline_metadata_cleanup_and_alias_guards() {
  $python=getenv('ADB_STATIC_ANALYSIS_PYTHON')?:'/usr/bin/python3';
  $p=proc_open(array($python,'-I','-B',__DIR__.'/support/source_policy_legacy_ubi_test.py'),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
  $this->assertIsResource($p);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
  $this->assertSame(0,proc_close($p),$err);$this->assertSame('',$err);
  $this->assertSame(array('tests'=>4,'failures'=>0,'errors'=>0,'network_calls'=>0),json_decode($out,true));
 }
 public function test_static_workflow_has_no_candidate_or_database_execution() {
  $root=dirname(__DIR__);$source=file_get_contents($root.'/scripts/ci/source-policy-legacy-ubi-static.py');
  foreach(array('subprocess','extractall(','os.system','ctypes','docker pull') as $bad)$this->assertStringNotContainsString($bad,$source);
  $workflow=file_get_contents($root.'/.github/workflows/source-policy-legacy-ubi-static.yml');
  $this->assertStringContainsString('if: always()',$workflow);
  $this->assertStringContainsString('persist-credentials: false',$workflow);
  $this->assertStringNotContainsString('schedule:',$workflow);
  $this->assertStringNotContainsString('legacy-ubi',file_get_contents($root.'/alynt-drime-backups-dashboard.php'));
 }
}
