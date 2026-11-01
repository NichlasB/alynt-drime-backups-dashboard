<?php
use PHPUnit\Framework\TestCase;

final class SourcePolicyUbiAliasTest extends TestCase {
 public function test_actual_static_collector_and_fixed_alias_contracts() {
  $python=getenv('ADB_STATIC_ANALYSIS_PYTHON')?:'/usr/bin/python3';
  $p=proc_open(array($python,'-I','-B',__DIR__.'/support/source_policy_ubi_alias_test.py'),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
  $this->assertIsResource($p);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
  $this->assertSame(0,proc_close($p),$err);$this->assertSame('',$err);
  $this->assertSame(array('tests'=>8,'failures'=>0,'errors'=>0,'network_calls'=>0),json_decode($out,true));
 }
 public function test_exception_is_explicit_static_only_and_default_reader_remains() {
  $root=dirname(__DIR__);$entry=file_get_contents($root.'/scripts/ci/source-policy-legacy-ubi-static.py');
  $workflow=file_get_contents($root.'/.github/workflows/source-policy-legacy-ubi-static.yml');
  $this->assertStringContainsString('def proof(root, alias_policy=False):',$entry);
  $this->assertStringContainsString('scan = s.scan_layer(path, diff, WANTED, roles, budget)',$entry);
  $this->assertStringContainsString("s.require(alias.PIN == PIN and alias.CONFIG == CONFIG, 'alias-candidate-binding')",$entry);
  $this->assertStringContainsString("'runtime_grant': False",$entry);
  $this->assertStringContainsString('default: false',$workflow);
  $this->assertStringContainsString('ALIAS_POLICY:',$workflow);
  $this->assertStringContainsString('alias.approved_profile(collected, i), hardlinks=True)',$entry);
  foreach(array('subprocess','extractall(','os.system','docker pull') as $bad)$this->assertStringNotContainsString($bad,$entry);
 }
 public function test_actual_static_hardlink_content_origin_contracts() {
  $python=getenv('ADB_STATIC_ANALYSIS_PYTHON')?:'/usr/bin/python3';
  $p=proc_open(array($python,'-I','-B',__DIR__.'/support/source_policy_ubi_hardlink_test.py'),array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
  $this->assertIsResource($p);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
  $this->assertSame(0,proc_close($p),$err);$this->assertSame('',$err);
  $this->assertSame(array('tests'=>10,'failures'=>0,'errors'=>0,'network_calls'=>0),json_decode($out,true));
 }
}
