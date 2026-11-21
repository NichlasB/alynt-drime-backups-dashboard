<?php
use PHPUnit\Framework\TestCase;

final class SourcePolicyPpaDebStaticTest extends TestCase {
 public function test_synthetic_deb_policy_without_acquisition() {
  $python=getenv('ADB_STATIC_ANALYSIS_PYTHON')?:'/usr/bin/python3';
  $p=proc_open(array($python,'-I','-S','-B',__DIR__.'/support/source_policy_ppa_deb_test.py'),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
  $this->assertIsResource($p);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
  $this->assertSame(0,proc_close($p),$err);$this->assertSame('',$err);
  $this->assertSame(array('tests'=>PHP_OS_FAMILY==='Linux'?16:15,'failures'=>0,'errors'=>0,'network_calls'=>0,'linux_codec_test'=>PHP_OS_FAMILY==='Linux'),json_decode($out,true));
 }
 public function test_manual_only_workflow_and_no_runtime_wiring() {
  $root=dirname(__DIR__);$workflow=file_get_contents($root.'/.github/workflows/source-policy-ppa-deb-static.yml');
  foreach(array('if: always()','persist-credentials: false','timeout-minutes: 10','GITHUB_RUN_ATTEMPT','EXPECTED_SHA') as $guard)$this->assertStringContainsString($guard,$workflow);
  foreach(array('schedule:','setup-php','apt-get','docker','upload-artifact') as $bad)$this->assertStringNotContainsString($bad,$workflow);
  $source=file_get_contents($root.'/scripts/ci/source-policy-ppa-deb-static.py').file_get_contents($root.'/scripts/ci/source_policy_deb_data.py');
  foreach(array('extractall(','.extract(','ctypes','os.system','shell=True','ldd','dpkg -i') as $bad)$this->assertStringNotContainsString($bad,$source);
  $this->assertStringNotContainsString('ppa-deb-static',file_get_contents($root.'/alynt-drime-backups-dashboard.php'));
 }
}
