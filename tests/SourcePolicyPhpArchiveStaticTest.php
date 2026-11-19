<?php
use PHPUnit\Framework\TestCase;

final class SourcePolicyPhpArchiveStaticTest extends TestCase {
 public function test_synthetic_archive_and_owned_cleanup_guards() {
  $python=getenv('ADB_STATIC_ANALYSIS_PYTHON')?:'/usr/bin/python3';
  $p=proc_open(array($python,'-I','-S','-B',__DIR__.'/support/source_policy_php_archive_test.py'),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
  $this->assertIsResource($p);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
  $this->assertSame(0,proc_close($p),$err);$this->assertSame('',$err);
  $this->assertSame(array('tests'=>9,'failures'=>0,'errors'=>0,'network_calls'=>0),json_decode($out,true));
 }
 public function test_manual_workflow_and_static_only_boundary() {
  $root=dirname(__DIR__);$source=file_get_contents($root.'/scripts/ci/source-policy-php-archive-static.py');
  foreach(array('extractall(','.extract(','ctypes','os.system','shell=True','ldd','php --version','php -m') as $bad)$this->assertStringNotContainsString($bad,$source);
  $workflow=file_get_contents($root.'/.github/workflows/source-policy-php-archive-static.yml');
  foreach(array('if: always()','persist-credentials: false','timeout-minutes: 10','GITHUB_RUN_ATTEMPT') as $guard)$this->assertStringContainsString($guard,$workflow);
  foreach(array('schedule:','setup-php','apt-get','docker','upload-artifact') as $bad)$this->assertStringNotContainsString($bad,$workflow);
  $this->assertStringNotContainsString('php-archive-static',file_get_contents($root.'/alynt-drime-backups-dashboard.php'));
 }
}
